<?php

declare(strict_types=1);

namespace App\Service\Templates\Property;

use App\Entity\Contracts;
use App\Entity\ContractsProperty;
use App\Entity\Enum\PropertyKind;
use App\Entity\Enum\PropertySubtype;
use App\Service\MoneyHelperService;
use Doctrine\Common\Collections\ArrayCollection;

class PropertyMethods
{
    private const string INVENTORY_DASH = '—';
    private const string REPORT_DASH = '–';
    private const string ZERO_AMOUNT = '0.00';
    private const string NOT_FOUND_TEXT = 'не выявлено';
    private const string LIST_SEPARATOR = '; ';
    private const string SUBTYPE_SEPARATOR = ': ';
    private const string EXCLUSION_SEPARATOR = ', ';

    /**
     * @return ArrayCollection<int, PropertyRow>
     */
    public static function inventoryRows(Contracts $contract, PropertyKind $kind): ArrayCollection
    {
        $items = self::itemsOfKind(contract: $contract, kind: $kind);

        if ($items === []) {
            return new ArrayCollection(array_map(
                static fn (PropertySubtype $subtype): PropertyRow => self::emptySubtypeRow(subtype: $subtype),
                $kind->getSubtypes(),
            ));
        }

        return new ArrayCollection(array_map(
            static fn (ContractsProperty $property): PropertyRow => self::itemRow(
                property: $property,
                withSubtype: true,
                dash: self::INVENTORY_DASH,
            ),
            $items,
        ));
    }

    /**
     * @return ArrayCollection<int, PropertyRow>
     */
    public static function reportRows(Contracts $contract, PropertyKind $kind): ArrayCollection
    {
        $items = self::itemsOfKind(contract: $contract, kind: $kind);

        if ($items === []) {
            return new ArrayCollection([self::emptyReportRow()]);
        }

        return new ArrayCollection(array_map(
            static fn (ContractsProperty $property): PropertyRow => self::itemRow(
                property: $property,
                withSubtype: false,
                dash: self::REPORT_DASH,
            ),
            $items,
        ));
    }

    public static function managerTotal(Contracts $contract, ?PropertyKind $kind = null): string
    {
        return self::total(
            contract: $contract,
            kind: $kind,
            amount: static fn (ContractsProperty $property): ?string => $property->getManagerValuation(),
        );
    }

    public static function appraiserTotal(Contracts $contract, ?PropertyKind $kind = null): string
    {
        return self::total(
            contract: $contract,
            kind: $kind,
            amount: static fn (ContractsProperty $property): ?string => $property->getAppraiserValuation(),
        );
    }

    public static function excludedTotal(Contracts $contract, ?PropertyKind $kind = null): string
    {
        return self::total(
            contract: $contract,
            kind: $kind,
            amount: static fn (ContractsProperty $property): ?string => $property->getExcludedValuation(),
        );
    }

    public static function summaryText(Contracts $contract, PropertyKind $kind): string
    {
        $items = self::itemsOfKind(contract: $contract, kind: $kind);

        if ($items === []) {
            return self::NOT_FOUND_TEXT;
        }

        return implode(self::LIST_SEPARATOR, array_map(
            static fn (ContractsProperty $property): string => self::itemDescription(property: $property),
            $items,
        ));
    }

    /**
     * @return array<int, ContractsProperty>
     */
    private static function itemsOfKind(Contracts $contract, PropertyKind $kind): array
    {
        $items = array_values(array_filter(
            $contract->getProperty()->toArray(),
            static fn (ContractsProperty $property): bool => $property->getKind() === $kind,
        ));

        usort(
            $items,
            static fn (ContractsProperty $first, ContractsProperty $second): int => array_search($first->getSubtype(), PropertySubtype::cases(), true)
                <=> array_search($second->getSubtype(), PropertySubtype::cases(), true),
        );

        return $items;
    }

    private static function itemRow(ContractsProperty $property, bool $withSubtype, string $dash): PropertyRow
    {
        $name = $withSubtype
            ? $property->getSubtype()->getLabel() . self::SUBTYPE_SEPARATOR . $property->getName()
            : $property->getName();

        return new PropertyRow(
            name: $name,
            ownershipType: self::textOrDash(value: $property->getOwnershipType(), dash: $dash),
            location: self::textOrDash(value: $property->getLocation(), dash: $dash),
            area: self::textOrDash(value: $property->getArea(), dash: $dash),
            identificationNumber: self::textOrDash(value: $property->getIdentificationNumber(), dash: $dash),
            pledgeInfo: self::textOrDash(value: $property->getPledgeInfo(), dash: $dash),
            managerValuation: MoneyHelperService::formatPlain(amount: $property->getManagerValuation()),
            appraiserValuation: MoneyHelperService::formatPlain(amount: $property->getAppraiserValuation()),
            exclusionText: self::exclusionText(property: $property),
        );
    }

    private static function emptySubtypeRow(PropertySubtype $subtype): PropertyRow
    {
        return new PropertyRow(
            name: $subtype->getLabel() . ':',
            ownershipType: self::INVENTORY_DASH,
            location: self::INVENTORY_DASH,
            area: self::INVENTORY_DASH,
            identificationNumber: self::INVENTORY_DASH,
            pledgeInfo: self::INVENTORY_DASH,
            managerValuation: MoneyHelperService::formatPlain(amount: self::ZERO_AMOUNT),
            appraiserValuation: MoneyHelperService::formatPlain(amount: self::ZERO_AMOUNT),
            exclusionText: MoneyHelperService::formatPlain(amount: self::ZERO_AMOUNT),
        );
    }

    private static function emptyReportRow(): PropertyRow
    {
        return new PropertyRow(
            name: self::REPORT_DASH,
            ownershipType: self::REPORT_DASH,
            location: self::REPORT_DASH,
            area: self::REPORT_DASH,
            identificationNumber: self::REPORT_DASH,
            pledgeInfo: self::REPORT_DASH,
            managerValuation: self::REPORT_DASH,
            appraiserValuation: self::REPORT_DASH,
            exclusionText: self::REPORT_DASH,
        );
    }

    private static function exclusionText(ContractsProperty $property): string
    {
        $amount = MoneyHelperService::formatPlain(amount: $property->getExcludedValuation());
        $reason = trim((string)$property->getExclusionReason());

        if ($reason === '') {
            return $amount;
        }

        return $amount . self::EXCLUSION_SEPARATOR . $reason;
    }

    private static function itemDescription(ContractsProperty $property): string
    {
        $parts = array_filter([
            $property->getName(),
            trim((string)$property->getLocation()),
        ]);

        return implode(self::EXCLUSION_SEPARATOR, $parts);
    }

    /**
     * @param callable(ContractsProperty): ?string $amount
     */
    private static function total(Contracts $contract, ?PropertyKind $kind, callable $amount): string
    {
        $items = $kind === null
            ? $contract->getProperty()->toArray()
            : self::itemsOfKind(contract: $contract, kind: $kind);

        $total = self::ZERO_AMOUNT;

        foreach ($items as $property) {
            $total = MoneyHelperService::add(first: $total, second: $amount($property));
        }

        return MoneyHelperService::formatPlain(amount: $total);
    }

    private static function textOrDash(?string $value, string $dash): string
    {
        $text = trim((string)$value);

        return $text === '' ? $dash : $text;
    }
}
