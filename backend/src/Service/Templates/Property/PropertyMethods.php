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
    public const string NOT_FOUND_NEUTER = 'не выявлено';
    public const string NOT_FOUND_PLURAL = 'не выявлены';
    public const string NOT_FOUND_FEMININE = 'не выявлена';

    private const string INVENTORY_DASH = '—';
    private const string REPORT_DASH = '–';
    private const string ZERO_AMOUNT = '0.00';
    private const string LIST_SEPARATOR = '; ';
    private const string SUBTYPE_SEPARATOR = ': ';
    private const string PART_SEPARATOR = ', ';
    private const string DATE_FORMAT = 'd.m.Y';

    /**
     * @return ArrayCollection<int, PropertyRow>
     */
    public static function inventoryRows(Contracts $contract, PropertyKind $kind): ArrayCollection
    {
        $items = self::itemsOfKinds(contract: $contract, kinds: [$kind]);

        if ($items === []) {
            if (!$kind->hasSubtypeLabels()) {
                return new ArrayCollection([self::emptyRow(dash: self::INVENTORY_DASH, name: self::INVENTORY_DASH)]);
            }

            return new ArrayCollection(array_map(
                static fn (PropertySubtype $subtype): PropertyRow => self::emptyRow(
                    dash: self::INVENTORY_DASH,
                    name: $subtype->getLabel() . ':',
                ),
                $kind->getSubtypes(),
            ));
        }

        return new ArrayCollection(array_map(
            static fn (ContractsProperty $property): PropertyRow => self::itemRow(
                property: $property,
                withSubtype: $kind->hasSubtypeLabels(),
                dash: self::INVENTORY_DASH,
            ),
            $items,
        ));
    }

    /**
     * @param array<int, PropertyKind> $kinds
     *
     * @return ArrayCollection<int, PropertyRow>
     */
    public static function reportRows(Contracts $contract, array $kinds): ArrayCollection
    {
        $items = self::itemsOfKinds(contract: $contract, kinds: $kinds);

        if ($items === []) {
            return new ArrayCollection([self::emptyRow(dash: self::REPORT_DASH, name: self::REPORT_DASH)]);
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

    /**
     * @param array<int, PropertyKind> $kinds
     */
    public static function managerTotal(Contracts $contract, array $kinds = []): string
    {
        return self::total(
            contract: $contract,
            kinds: $kinds,
            amount: static fn (ContractsProperty $property): ?string => $property->getManagerValuation(),
        );
    }

    /**
     * @param array<int, PropertyKind> $kinds
     */
    public static function appraiserTotal(Contracts $contract, array $kinds = []): string
    {
        return self::total(
            contract: $contract,
            kinds: $kinds,
            amount: static fn (ContractsProperty $property): ?string => $property->getAppraiserValuation(),
        );
    }

    /**
     * @param array<int, PropertyKind> $kinds
     */
    public static function excludedTotal(Contracts $contract, array $kinds = []): string
    {
        return self::total(
            contract: $contract,
            kinds: $kinds,
            amount: static fn (ContractsProperty $property): ?string => $property->getExcludedValuation(),
        );
    }

    /**
     * @param array<int, PropertyKind> $kinds
     */
    public static function summaryText(Contracts $contract, array $kinds, string $notFound = self::NOT_FOUND_NEUTER): string
    {
        $items = self::itemsOfKinds(contract: $contract, kinds: $kinds);

        if ($items === []) {
            return $notFound;
        }

        return implode(self::LIST_SEPARATOR, array_map(
            static fn (ContractsProperty $property): string => self::itemDescription(property: $property),
            $items,
        ));
    }

    /**
     * @param array<int, PropertyKind> $kinds
     *
     * @return array<int, ContractsProperty>
     */
    private static function itemsOfKinds(Contracts $contract, array $kinds): array
    {
        $items = array_values(array_filter(
            $contract->getProperty()->toArray(),
            static fn (ContractsProperty $property): bool => $kinds === [] || in_array($property->getKind(), $kinds, true),
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
            accountType: self::textOrDash(value: $property->getAccountType(), dash: $dash),
            openedAt: self::textOrDash(value: $property->getOpenedAt()?->format(self::DATE_FORMAT), dash: $dash),
            amount: self::amountOrDash(amount: $property->getAmount(), dash: $dash),
            currency: self::textOrDash(value: $property->getCurrency(), dash: $dash),
            issuer: self::textOrDash(value: $property->getIssuer(), dash: $dash),
            participationShare: self::textOrDash(value: $property->getParticipationShare(), dash: $dash),
            quantity: self::textOrDash(value: $property->getQuantity(), dash: $dash),
            obligationContent: self::textOrDash(value: $property->getObligationContent(), dash: $dash),
            basisText: self::textOrDash(value: $property->getBasisText(), dash: $dash),
        );
    }

    private static function emptyRow(string $dash, string $name): PropertyRow
    {
        $valuation = $dash === self::REPORT_DASH ? $dash : MoneyHelperService::formatPlain(amount: self::ZERO_AMOUNT);

        return new PropertyRow(
            name: $name,
            ownershipType: $dash,
            location: $dash,
            area: $dash,
            identificationNumber: $dash,
            pledgeInfo: $dash,
            managerValuation: $valuation,
            appraiserValuation: $valuation,
            exclusionText: $valuation,
            accountType: $dash,
            openedAt: $dash,
            amount: $dash,
            currency: $dash,
            issuer: $dash,
            participationShare: $dash,
            quantity: $dash,
            obligationContent: $dash,
            basisText: $dash,
        );
    }

    private static function exclusionText(ContractsProperty $property): string
    {
        $amount = MoneyHelperService::formatPlain(amount: $property->getExcludedValuation());
        $reason = trim((string)$property->getExclusionReason());

        if ($reason === '') {
            return $amount;
        }

        return $amount . self::PART_SEPARATOR . $reason;
    }

    private static function itemDescription(ContractsProperty $property): string
    {
        $parts = array_filter([
            $property->getName(),
            trim((string)$property->getLocation()),
        ]);

        return implode(self::PART_SEPARATOR, $parts);
    }

    /**
     * @param array<int, PropertyKind> $kinds
     * @param callable(ContractsProperty): ?string $amount
     */
    private static function total(Contracts $contract, array $kinds, callable $amount): string
    {
        $total = self::ZERO_AMOUNT;

        foreach (self::itemsOfKinds(contract: $contract, kinds: $kinds) as $property) {
            $total = MoneyHelperService::add(first: $total, second: $amount($property));
        }

        return MoneyHelperService::formatPlain(amount: $total);
    }

    private static function textOrDash(?string $value, string $dash): string
    {
        $text = trim((string)$value);

        return $text === '' ? $dash : $text;
    }

    private static function amountOrDash(?string $amount, string $dash): string
    {
        $normalized = MoneyHelperService::normalize(amount: $amount);

        return $normalized === null ? $dash : MoneyHelperService::formatPlain(amount: $normalized);
    }
}
