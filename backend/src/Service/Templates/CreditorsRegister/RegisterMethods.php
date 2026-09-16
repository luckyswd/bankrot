<?php

declare(strict_types=1);

namespace App\Service\Templates\CreditorsRegister;

use App\Entity\Contracts;
use App\Entity\ContractsCreditorsClaim;
use App\Service\MoneyHelperService;
use Doctrine\Common\Collections\ArrayCollection;

class RegisterMethods
{
    private const string DATE_FORMAT = 'd.m.Y';
    private const string SORTABLE_DATE_FORMAT = 'Y-m-d';
    private const string STORED_DATE_FORMAT = '!Y-m-d';
    private const int STORED_DATE_LENGTH = 10;
    private const string YEAR_SUFFIX = ' г.';
    private const string ZERO_AMOUNT = '0.00';
    private const string LIST_SEPARATOR = ', ';
    private const string SINGLE_BASIS_PREFIX = 'Кредитный договор ';
    private const string MULTIPLE_BASIS_PREFIX = 'Кредитные договоры ';
    private const string DOCUMENT_NUMBER_PREFIX = '№ ';
    private const string DATE_PREFIX = 'от ';
    private const string CREDIT_CARD_TEXT = 'договор обслуживания кредитной карты от %s';
    private const string CREDIT_CARD_JOINER = ' и ';
    private const string DETERMINATION_PREFIX = 'Определение Арбитражного суда';
    private const string CASE_NUMBER_PREFIX = 'по делу № ';
    private const string SANCTION_PENALTY = 'штраф';
    private const string SANCTION_LATE_FEE = 'пени';
    private const string SANCTION_FORFEITURE = 'неустойка';

    /**
     * @return ArrayCollection<int, RegistryClaimRow>
     */
    public static function mainClaims(Contracts $contract): ArrayCollection
    {
        return self::rows(contract: $contract, part: RegistryPart::MAIN);
    }

    /**
     * @return ArrayCollection<int, RegistryClaimRow>
     */
    public static function sanctionClaims(Contracts $contract): ArrayCollection
    {
        return self::rows(contract: $contract, part: RegistryPart::SANCTION);
    }

    public static function mainCreditorsCount(Contracts $contract): int
    {
        return self::creditorsCount(contract: $contract, part: RegistryPart::MAIN);
    }

    public static function sanctionCreditorsCount(Contracts $contract): int
    {
        return self::creditorsCount(contract: $contract, part: RegistryPart::SANCTION);
    }

    public static function mainClaimsCount(Contracts $contract): int
    {
        return count(self::numberedClaims(contract: $contract, part: RegistryPart::MAIN));
    }

    public static function sanctionClaimsCount(Contracts $contract): int
    {
        return count(self::numberedClaims(contract: $contract, part: RegistryPart::SANCTION));
    }

    public static function mainAmountText(Contracts $contract): string
    {
        return MoneyHelperService::formatPlain(amount: self::totalAmount(contract: $contract, part: RegistryPart::MAIN));
    }

    public static function sanctionAmountText(Contracts $contract): string
    {
        return MoneyHelperService::formatPlain(amount: self::totalAmount(contract: $contract, part: RegistryPart::SANCTION));
    }

    public static function mainRepaidAmountText(Contracts $contract): string
    {
        return MoneyHelperService::formatPlain(amount: self::totalRepaid(contract: $contract, part: RegistryPart::MAIN));
    }

    public static function sanctionRepaidAmountText(Contracts $contract): string
    {
        return MoneyHelperService::formatPlain(amount: self::totalRepaid(contract: $contract, part: RegistryPart::SANCTION));
    }

    public static function mainRepaidPercentText(Contracts $contract): string
    {
        return self::repaidPercent(contract: $contract, part: RegistryPart::MAIN);
    }

    public static function sanctionRepaidPercentText(Contracts $contract): string
    {
        return self::repaidPercent(contract: $contract, part: RegistryPart::SANCTION);
    }

    public static function openingDateShort(Contracts $contract): string
    {
        return self::formatShortDate(date: $contract->getProcedureInitiationKommersantPublicationDate());
    }

    public static function closingDateShort(Contracts $contract): string
    {
        return self::formatShortDate(date: $contract->getRegistryClosingDate());
    }

    public static function registrationAddressWithPostalCode(Contracts $contract): string
    {
        $parts = [trim((string)$contract->getPostalCode()), trim((string)$contract->getFullRegistrationAddress())];

        return implode(self::LIST_SEPARATOR, array_filter($parts, static fn (string $part): bool => $part !== ''));
    }

    public static function mainAmount(ContractsCreditorsClaim $claim): string
    {
        $components = [
            $claim->getPrincipalAmount(),
            $claim->getInterest(),
            $claim->getStateDuty(),
            $claim->getStateDutyForConsideration(),
        ];

        $filledComponents = array_filter($components, static fn (?string $amount): bool => trim((string)$amount) !== '');

        if ($filledComponents !== []) {
            return MoneyHelperService::add(...$components);
        }

        return MoneyHelperService::max(
            first: MoneyHelperService::subtract(minuend: $claim->getDebtAmount(), subtrahend: self::sanctionAmount(claim: $claim)),
            second: self::ZERO_AMOUNT,
        );
    }

    public static function sanctionAmount(ContractsCreditorsClaim $claim): string
    {
        return MoneyHelperService::add($claim->getPenalty(), $claim->getLateFee(), $claim->getForfeiture());
    }

    public static function repaidAmount(ContractsCreditorsClaim $claim, RegistryPart $part): string
    {
        $repaid = MoneyHelperService::max(first: $claim->getRepaidAmount(), second: self::ZERO_AMOUNT);
        $repaidMain = MoneyHelperService::min(first: $repaid, second: self::mainAmount(claim: $claim));

        return match ($part) {
            RegistryPart::MAIN => $repaidMain,
            RegistryPart::SANCTION => MoneyHelperService::min(
                first: MoneyHelperService::subtract(minuend: $repaid, subtrahend: $repaidMain),
                second: self::sanctionAmount(claim: $claim),
            ),
        };
    }

    /**
     * @return ArrayCollection<int, RegistryClaimRow>
     */
    private static function rows(Contracts $contract, RegistryPart $part): ArrayCollection
    {
        $rows = [];

        foreach (self::numberedClaims(contract: $contract, part: $part) as $entry) {
            $rows[] = self::row(contract: $contract, part: $part, entry: $entry);
        }

        return new ArrayCollection($rows);
    }

    /**
     * @param array{claim: ContractsCreditorsClaim, claimNumber: int, creditorNumber: int} $entry
     */
    private static function row(Contracts $contract, RegistryPart $part, array $entry): RegistryClaimRow
    {
        $claim = $entry['claim'];
        $creditor = $claim->getCreditor();

        return new RegistryClaimRow(
            entryDate: self::formatDate(date: $claim->getRegistryEntryDate()),
            creditorNumber: $entry['creditorNumber'],
            claimNumber: $entry['claimNumber'],
            creditorName: $creditor->getName(),
            creditorAddress: trim((string)$creditor->getAddress()),
            creditorHeadFullName: trim((string)$creditor->getHeadFullName()),
            creditorBankDetails: trim((string)$creditor->getBankDetails()),
            kind: $part === RegistryPart::MAIN ? trim((string)$claim->getObligationType()) : self::sanctionKind(claim: $claim),
            basisText: self::basisText(claim: $claim),
            originDate: self::formatDate(date: $claim->getOriginDate()),
            amountText: MoneyHelperService::formatPlain(amount: self::partAmount(claim: $claim, part: $part)),
            determinationText: self::determinationText(contract: $contract, claim: $claim),
        );
    }

    /**
     * @return array<int, array{claim: ContractsCreditorsClaim, claimNumber: int, creditorNumber: int}>
     */
    private static function numberedClaims(Contracts $contract, RegistryPart $part): array
    {
        $entries = [];
        $creditorNumbers = [];

        foreach (self::includedClaims(contract: $contract) as $index => $claim) {
            $creditorKey = spl_object_id($claim->getCreditor());
            $creditorNumbers[$creditorKey] ??= count($creditorNumbers) + 1;

            if (!MoneyHelperService::isPositive(amount: self::partAmount(claim: $claim, part: $part))) {
                continue;
            }

            $entries[] = [
                'claim' => $claim,
                'claimNumber' => $index + 1,
                'creditorNumber' => $creditorNumbers[$creditorKey],
            ];
        }

        return $entries;
    }

    /**
     * @return array<int, ContractsCreditorsClaim>
     */
    private static function includedClaims(Contracts $contract): array
    {
        $entries = [];

        foreach (array_values($contract->getCreditorsClaims()->toArray()) as $position => $claim) {
            $entryDate = $claim->getRegistryEntryDate();

            if ($entryDate === null) {
                continue;
            }

            $entries[] = [
                'sortKey' => [$entryDate->format(self::SORTABLE_DATE_FORMAT), $claim->getId() ?? PHP_INT_MAX, $position],
                'claim' => $claim,
            ];
        }

        usort($entries, static fn (array $first, array $second): int => $first['sortKey'] <=> $second['sortKey']);

        return array_column($entries, 'claim');
    }

    private static function partAmount(ContractsCreditorsClaim $claim, RegistryPart $part): string
    {
        return match ($part) {
            RegistryPart::MAIN => self::mainAmount(claim: $claim),
            RegistryPart::SANCTION => self::sanctionAmount(claim: $claim),
        };
    }

    private static function creditorsCount(Contracts $contract, RegistryPart $part): int
    {
        return count(array_unique(array_column(self::numberedClaims(contract: $contract, part: $part), 'creditorNumber')));
    }

    private static function totalAmount(Contracts $contract, RegistryPart $part): string
    {
        $amounts = [];

        foreach (self::numberedClaims(contract: $contract, part: $part) as $entry) {
            $amounts[] = self::partAmount(claim: $entry['claim'], part: $part);
        }

        return MoneyHelperService::add(...$amounts);
    }

    private static function totalRepaid(Contracts $contract, RegistryPart $part): string
    {
        $amounts = [];

        foreach (self::numberedClaims(contract: $contract, part: $part) as $entry) {
            $amounts[] = self::repaidAmount(claim: $entry['claim'], part: $part);
        }

        return MoneyHelperService::add(...$amounts);
    }

    private static function repaidPercent(Contracts $contract, RegistryPart $part): string
    {
        return MoneyHelperService::percent(
            part: self::totalRepaid(contract: $contract, part: $part),
            total: self::totalAmount(contract: $contract, part: $part),
        );
    }

    private static function sanctionKind(ContractsCreditorsClaim $claim): string
    {
        $sanctions = [
            self::SANCTION_PENALTY => $claim->getPenalty(),
            self::SANCTION_LATE_FEE => $claim->getLateFee(),
            self::SANCTION_FORFEITURE => $claim->getForfeiture(),
        ];

        $kinds = array_keys(array_filter($sanctions, static fn (?string $amount): bool => MoneyHelperService::isPositive(amount: $amount)));

        return self::capitalize(text: implode(self::LIST_SEPARATOR, $kinds));
    }

    private static function basisText(ContractsCreditorsClaim $claim): string
    {
        $documents = [];

        foreach ($claim->getBasis() ?? [] as $item) {
            /** @var mixed $item */
            if (!is_array($item)) {
                continue;
            }

            $document = self::basisDocument(item: $item);

            if ($document !== '') {
                $documents[] = $document;
            }
        }

        $text = match (count($documents)) {
            0 => '',
            1 => self::SINGLE_BASIS_PREFIX . $documents[0],
            default => self::MULTIPLE_BASIS_PREFIX . implode(self::LIST_SEPARATOR, $documents),
        };

        $creditCardText = self::creditCardText(claim: $claim);

        if ($creditCardText === '') {
            return $text;
        }

        return $text === '' ? self::capitalize(text: $creditCardText) : $text . self::CREDIT_CARD_JOINER . $creditCardText;
    }

    /**
     * @param array<array-key, mixed> $item
     */
    private static function basisDocument(array $item): string
    {
        $number = is_string($item['number'] ?? null) ? trim($item['number']) : '';
        $date = is_string($item['date'] ?? null) ? self::formatStoredDate(date: $item['date']) : '';

        $parts = [];

        if ($number !== '') {
            $parts[] = self::DOCUMENT_NUMBER_PREFIX . $number;
        }

        if ($date !== '') {
            $parts[] = self::DATE_PREFIX . $date . self::YEAR_SUFFIX;
        }

        return implode(' ', $parts);
    }

    private static function creditCardText(ContractsCreditorsClaim $claim): string
    {
        $creditCardDate = $claim->getCreditCardDate();

        if ($claim->getIsCreditCard() !== true || $creditCardDate === null) {
            return '';
        }

        return sprintf(self::CREDIT_CARD_TEXT, self::formatShortDate(date: $creditCardDate));
    }

    private static function determinationText(Contracts $contract, ContractsCreditorsClaim $claim): string
    {
        $parts = [self::DETERMINATION_PREFIX];

        $courtName = trim((string)$contract->getCourt()?->getShortName());

        if ($courtName !== '') {
            $parts[] = $courtName;
        }

        $judicialActDate = $claim->getJudicialActDate();

        if ($judicialActDate !== null) {
            $parts[] = self::DATE_PREFIX . self::formatShortDate(date: $judicialActDate);
        }

        $caseNumber = trim((string)$claim->getDisputeNumber());

        if ($caseNumber === '') {
            $caseNumber = trim((string)$contract->getCaseNumber());
        }

        if ($caseNumber !== '') {
            $parts[] = self::CASE_NUMBER_PREFIX . $caseNumber;
        }

        return implode(' ', $parts);
    }

    private static function formatDate(?\DateTimeInterface $date): string
    {
        return $date?->format(self::DATE_FORMAT) ?? '';
    }

    private static function formatShortDate(?\DateTimeInterface $date): string
    {
        return $date === null ? '' : $date->format(self::DATE_FORMAT) . self::YEAR_SUFFIX;
    }

    private static function formatStoredDate(string $date): string
    {
        $trimmed = trim($date);
        $parsed = \DateTime::createFromFormat(self::STORED_DATE_FORMAT, substr($trimmed, 0, self::STORED_DATE_LENGTH));

        return $parsed === false ? $trimmed : $parsed->format(self::DATE_FORMAT);
    }

    private static function capitalize(string $text): string
    {
        return mb_strtoupper(mb_substr($text, 0, 1)) . mb_substr($text, 1);
    }
}
