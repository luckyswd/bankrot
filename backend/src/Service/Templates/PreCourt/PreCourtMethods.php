<?php

declare(strict_types=1);

namespace App\Service\Templates\PreCourt;

use App\Entity\Contracts;

/**
 * Для шаблонов Досудебка.
 */
class PreCourtMethods
{
    /**
     * Заявление о признании банкротом
     */
    public static function hasEnforcementProceedingsDescription(Contracts $contract): string
    {
        if ($contract->hasEnforcementProceedings()) {
            return 'У Должника имеются возбужденные исполнительные производства.';
        } else {
            return 'У Должника не имеются возбужденные исполнительные производства.';
        }
    }

    /**
     * Заявление о признании банкротом
     */
    public static function workDescription(Contracts $contract): string
    {
        if ($contract->getWork()) {
            return 'Должник временно трудоустроен.';
        } else {
            return 'Должник временно не трудоустроен.';
        }
    }

    /**
     * Заявление о признании банкротом
     */
    public static function maritalStatusDescription(Contracts $contract): string
    {
        $isMarried = $contract->getMaritalStatus() === 'married';
        $wasMarried3YearsAgo = $contract->getMaritalStatus() === 'married_3y_ago';
        $hasChildren = $contract->hasMinorChildren() === true;
        $divorceDate = $contract->getMarriageTerminationDate();

        $parts = [];

        if ($isMarried) {
            $parts[] = 'Должник состоит в браке';
        } else {
            $divorceInfo = '';
            if ($wasMarried3YearsAgo && $divorceDate !== null) {
                $formattedDate = $divorceDate->format('d.m.Y');
                $divorceInfo = " (брак расторгнут {$formattedDate}г.)";
            }
            $parts[] = 'Должник не состоит в браке' . $divorceInfo;
        }

        if ($hasChildren) {
            $parts[] = 'имеет несовершеннолетних детей';
        } else {
            $parts[] = 'не имеет несовершеннолетних детей';
        }

        return implode(', ', $parts) . '.';
    }
}
