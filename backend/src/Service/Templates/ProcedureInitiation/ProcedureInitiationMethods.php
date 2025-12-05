<?php

declare(strict_types=1);

namespace App\Service\Templates\ProcedureInitiation;

use App\Entity\Contracts;
use App\Service\DateHelperService;

/**
 * Для шаблонов Судебка введение процедуры.
 */
class ProcedureInitiationMethods
{
    /**
     * 1. Публикация ЕФРСБ.
     */
    public static function financialManagerReportHearingDescription(Contracts $contract): string
    {
        $hearingDateTime = $contract->getEfrsbDateTime();

        if ($hearingDateTime === null || $contract->getCourt() === null) {
            return '';
        }

        $date = $hearingDateTime->format('d');
        $month = DateHelperService::getMonthNameGenitive($hearingDateTime->format('n'));
        $year = $hearingDateTime->format('Y');
        $time = (int)$hearingDateTime->format('G');
        $minutes = $hearingDateTime->format('i');

        $court = $contract->getCourt();
        $courtName = $court->getShortName();
        $courtAddress = $court->getAddress() ?? '';

        $roomPart = '';

        if ($contract->getEfrsbCabinet() !== null && $contract->getEfrsbCabinet() !== '') {
            $roomPart = ', зал ' . $contract->getEfrsbCabinet();
        }

        return sprintf(
            'Судебное заседание по рассмотрению отчета финансового управляющего назначено на %s %s %s года в %s час. %s мин. в помещении Арбитражного суда %s по адресу: %s%s',
            $date,
            $month,
            $year,
            $time,
            $minutes,
            $courtName,
            $courtAddress,
            $roomPart
        );
    }

    /**
     * 1. Публикация ЕФРСБ.
     * 3. Уведомление о введении реализации имущества.
     */
    private static function textDeclaredInsolvent(Contracts $contract): string
    {
        if ($contract->getGender() === 'female') {
            return 'признана несостоятельной';
        }

        return 'признан несостоятельным';
    }

    /**
     * 2. Заявка на публикацию (газета Коммерсантъ).
     *
     * @example '24 июня 2025 г.'
     */
    public static function procedureInitiationDateForPublication(Contracts $contract): string
    {
        $date = $contract->getProcedureInitiationDecisionDate() ?? $contract->getProcedureInitiationResolutionDate();

        if ($date === null) {
            return '';
        }

        $day = $date->format('d');
        $month = DateHelperService::getMonthNameGenitive($date->format('n'));
        $year = $date->format('Y');

        return sprintf('«%s» %s %s г.', $day, $month, $year);
    }

    /**
     * 2. Заявка на публикацию (газета Коммерсантъ).
     * 3. Уведомление о введении реализации имущества.
     *
     * @example '12.12.2025 г. (резолютивная часть объявлена 10.12.2025 г.)'
     */
    public static function procedureInitiationDateWithResolution(Contracts $contract): string
    {
        $decisionDate = $contract->getProcedureInitiationDecisionDate();
        $resolutionDate = $contract->getProcedureInitiationResolutionDate();

        // Если есть обе даты
        if ($decisionDate !== null && $resolutionDate !== null) {
            $decisionFormatted = $decisionDate->format('d.m.Y');
            $resolutionFormatted = $resolutionDate->format('d.m.Y');

            return sprintf('%s г. (резолютивная часть объявлена %s г.)', $decisionFormatted, $resolutionFormatted);
        }

        // Если есть только дата решения
        if ($decisionDate !== null) {
            return $decisionDate->format('d.m.Y') . ' г.';
        }

        // Если есть только дата резолютивной части
        if ($resolutionDate !== null) {
            return $resolutionDate->format('d.m.Y') . ' г. (резолютивная часть)';
        }

        return '';
    }

    /**
     * 2. Заявка на публикацию (газета Коммерсантъ).
     *
     * @example 'заседание-25.11.24 в 10:05, зал 3052'
     */
    public static function efrsbHearingInfo(Contracts $contract): string
    {
        $efrsbDateTime = $contract->getEfrsbDateTime();

        if ($efrsbDateTime === null) {
            return '';
        }

        $date = $efrsbDateTime->format('d.m.y');
        $time = $efrsbDateTime->format('H:i');

        $result = sprintf('заседание-%s в %s', $date, $time);

        $cabinet = $contract->getEfrsbCabinet();

        if ($cabinet !== null && $cabinet !== '') {
            $result .= ', зал ' . $cabinet;
        }

        return $result;
    }

    /**
     * 3. Уведомление о введении реализации имущества.
     */
    public static function resolutionPart(Contracts $contract): string
    {
        if ($contract->getProcedureInitiationResolutionDate()) {
            return 'резолютивной части';
        }

        return '';
    }

    /**
     * 3. Уведомление о введении реализации имущества.
     */
    public static function spouse(Contracts $contract): string
    {
        $status = $contract->getMaritalStatus();
        $isFemale = $contract->getGender() === 'female';

        if ($status === 'married') {
            return $isFemale ? 'супругой' : 'супругом';
        }

        if ($status === 'married_3y_ago') {
            return $isFemale ? 'бывшей супругой' : 'бывшим супругом';
        }

        return '';
    }

    /**
     * В мноих шаблонах.
     */
    public static function spouseExtended(Contracts $contract): string
    {
        $status = $contract->getMaritalStatus();
        $isFemale = $contract->getGender() === 'female';

        // Текущие формы
        $current = $isFemale ? 'и его супруга' : 'и её супруги';
        $former  = $isFemale ? 'и его бывшего супруга' : 'и её бывшей супруги';

        if ($status === 'married') {
            return $current;
        }

        if ($status === 'married_3y_ago') {
            return $former;
        }

        return '';
    }

    /**
     * В многих шаблонах.
     */
    public static function startNotificationText(Contracts $contract): string
    {
        return sprintf(
            'Решением Арбитражного суда %s от %s по делу № %s %s (дата рождения: %s г., место рождения: %s, СНИЛС %s, ИНН %s, адрес регистрации: %s) %s (банкротом) и введена процедура реализации имущества гражданина сроком на %s.',
            $contract->getCourt()?->getShortName() ?? '',
            self::procedureInitiationDateWithResolution(contract: $contract),
            $contract->getProcedureInitiationCaseNumber() ?? '',
            $contract->getFullName() ?? '',
            $contract->getBirthDate()?->format('d.m.Y') ?? '',
            $contract->getBirthPlace() ?? '',
            $contract->getSnils() ?? '',
            $contract->getEmployerInn() ?? '',
            $contract->getFullRegistrationAddress() ?? '',
            self::textDeclaredInsolvent(contract: $contract),
            $contract->getProcedureInitiationDuration() ?? '',
        );
    }

    /**
     * 8. Заявление в ФССП об окончании ИП
     */
    public static function IPEndingsStr(Contracts $contract): string
    {
        $items = $contract->getProcedureInitiationIPEndings();

        if (!$items) {
            return '';
        }

        $parts = [];

        foreach ($items as $IPEnding) {
            $parts[] = $IPEnding['number'] . ' от ' . $IPEnding['date'];
        }

        return implode(', ', $parts);
    }
}
