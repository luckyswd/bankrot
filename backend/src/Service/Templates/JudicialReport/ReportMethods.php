<?php

declare(strict_types=1);

namespace App\Service\Templates\JudicialReport;

use App\Entity\Contracts;
use App\Entity\Enum\ProcedureExtensionStatus;
use App\Service\DateHelperService;

class ReportMethods
{
    private const string DASH = '–';
    private const string YEAR_SUFFIX = ' г.';
    private const string SHORT_DATE_FORMAT = 'd.m.Y';
    private const string STORED_DATE_FORMAT = '!Y-m-d';
    private const string SUSPICION_PERIOD = '-3 years';
    private const string GENDER_FEMALE = 'female';
    private const string MARITAL_STATUS_MARRIED = 'married';
    private const string MARITAL_STATUS_FORMERLY_MARRIED = 'married_3y_ago';
    private const string NOT_EXTENDED_TEXT = 'Процедура реализации имущества не продлевалась';
    private const string NO_ACTS_TEXT = 'Судебные акты не выносились';
    private const string NO_CHILDREN_TEXT = 'несовершеннолетних детей не находилось';
    private const string ONE_CHILD_TEXT = 'находился один несовершеннолетний ребёнок – %s';
    private const string SEVERAL_CHILDREN_TEXT = 'находилось %s несовершеннолетних детей – %s';
    private const string BIRTH_DATE_SUFFIX = ' г.р.';
    private const array COLLECTIVE_NUMERALS = [
        2 => 'двое',
        3 => 'трое',
        4 => 'четверо',
        5 => 'пятеро',
        6 => 'шестеро',
        7 => 'семеро',
        8 => 'восьмеро',
        9 => 'девятеро',
        10 => 'десятеро',
    ];

    public static function transactionsAnalysisStartDateText(Contracts $contract): string
    {
        $submissionDate = $contract->getCourtApplicationSubmissionDate();

        if ($submissionDate === null) {
            return '';
        }

        $startDate = \DateTimeImmutable::createFromInterface($submissionDate)->modify(self::SUSPICION_PERIOD);

        return DateHelperService::formatGenitive(date: $startDate);
    }

    public static function previousFullName(Contracts $contract): string
    {
        $changedLastName = trim((string)$contract->getChangedLastName());

        if ($contract->isLastNameChanged() === false || $changedLastName === '') {
            return self::DASH;
        }

        return $changedLastName;
    }

    public static function procedureExtensionText(Contracts $contract): string
    {
        return match ($contract->getProcedureExtensionStatus()) {
            ProcedureExtensionStatus::EXTENDED => self::formatStoredDates(dates: $contract->getProcedureExtensionDates() ?? []),
            ProcedureExtensionStatus::NOT_EXTENDED => self::NOT_EXTENDED_TEXT,
            ProcedureExtensionStatus::NO_ACTS => self::NO_ACTS_TEXT,
            null => '',
        };
    }

    public static function spouseInstrumental(Contracts $contract): string
    {
        $isFemale = $contract->getGender() === self::GENDER_FEMALE;

        return match ($contract->getMaritalStatus()) {
            self::MARITAL_STATUS_MARRIED => $isFemale ? ' и его супругом' : ' и его супругой',
            self::MARITAL_STATUS_FORMERLY_MARRIED => $isFemale ? ' и его бывшим супругом' : ' и его бывшей супругой',
            default => '',
        };
    }

    public static function shortFullNameGenitive(Contracts $contract): string
    {
        $lastName = trim((string)$contract->getLastNameGenitive());

        if ($lastName === '') {
            return '';
        }

        $initials = '';

        foreach ([$contract->getFirstName(), $contract->getMiddleName()] as $name) {
            $name = trim((string)$name);

            if ($name !== '') {
                $initials .= mb_substr($name, 0, 1) . '.';
            }
        }

        return trim($lastName . ' ' . $initials);
    }

    public static function procedureInitiationDateShort(Contracts $contract): string
    {
        $date = $contract->getProcedureInitiationDecisionDate() ?? $contract->getProcedureInitiationResolutionDate();

        if ($date === null) {
            return '';
        }

        return $date->format(self::SHORT_DATE_FORMAT) . self::YEAR_SUFFIX;
    }

    public static function zagsDepartmentText(Contracts $contract): string
    {
        $department = trim((string)$contract->getZagsDepartment());

        return $department === '' ? Contracts::DEFAULT_ZAGS_DEPARTMENT : $department;
    }

    public static function zagsCertificatePeriodText(Contracts $contract): string
    {
        $periodTo = $contract->getZagsCertificatePeriodTo();

        return implode(' ', array_filter([
            DateHelperService::formatGenitive(date: $contract->getZagsCertificatePeriodFrom()),
            $periodTo === null ? '' : 'по ' . DateHelperService::formatGenitive(date: $periodTo),
        ]));
    }

    public static function notifiedCreditorsList(Contracts $contract): string
    {
        $names = [];

        foreach ($contract->getPreCourtCreditors() as $preCourtCreditor) {
            $creditor = $preCourtCreditor->getCreditor();
            $names[spl_object_id($creditor)] = $creditor->getNameGenitive();
        }

        return implode(', ', $names);
    }

    public static function registryCreditorsCount(Contracts $contract): int
    {
        $creditors = [];

        foreach ($contract->getCreditorsClaims() as $creditorsClaim) {
            $creditors[spl_object_id($creditorsClaim->getCreditor())] = true;
        }

        return count($creditors);
    }

    public static function minorChildrenDependantsText(Contracts $contract): string
    {
        $children = array_map(
            static fn (array $child): string => self::describeChild(child: $child),
            $contract->getChildren() ?? [],
        );
        $count = count($children);

        if ($count === 0) {
            return self::NO_CHILDREN_TEXT;
        }

        if ($count === 1) {
            return sprintf(self::ONE_CHILD_TEXT, $children[0]);
        }

        $last = array_pop($children);

        return sprintf(
            self::SEVERAL_CHILDREN_TEXT,
            self::COLLECTIVE_NUMERALS[$count] ?? (string)$count,
            implode(', ', $children) . ' и ' . $last,
        );
    }

    /**
     * @param array{firstName?: string|null, lastName?: string|null, middleName?: string|null, birthDate?: string|null} $child
     */
    private static function describeChild(array $child): string
    {
        $fullName = implode(' ', array_filter(array_map(
            static fn (?string $part): string => trim((string)$part),
            [$child['lastName'] ?? null, $child['firstName'] ?? null, $child['middleName'] ?? null],
        )));

        $birthDate = \DateTimeImmutable::createFromFormat(self::STORED_DATE_FORMAT, (string)($child['birthDate'] ?? ''));

        if ($birthDate === false) {
            return $fullName;
        }

        return $fullName . ', ' . $birthDate->format(self::SHORT_DATE_FORMAT) . self::BIRTH_DATE_SUFFIX;
    }

    /**
     * @param array<int, string> $dates
     */
    private static function formatStoredDates(array $dates): string
    {
        $formatted = [];

        foreach ($dates as $date) {
            $parsed = \DateTimeImmutable::createFromFormat(self::STORED_DATE_FORMAT, $date);

            if ($parsed !== false) {
                $formatted[] = $parsed->format(self::SHORT_DATE_FORMAT) . self::YEAR_SUFFIX;
            }
        }

        return implode(', ', $formatted);
    }
}
