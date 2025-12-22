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
        $divorceDate = $contract->getMarriageTerminationDate();
        $children = $contract->getChildren() ?? [];

        $parts = [];

        if ($isMarried) {
            $parts[] = 'Должник состоит в браке';
        } else {
            $divorceInfo = '';
            if ($wasMarried3YearsAgo && $divorceDate !== null) {
                $divorceInfo = ' (брак расторгнут ' . $divorceDate->format('d.m.Y') . ' г.)';
            }
            $parts[] = 'Должник не состоит в браке' . $divorceInfo;
        }

        $childrenCount = count($children);

        if ($childrenCount === 0) {
            $parts[] = 'не имеет несовершеннолетних детей';
        } else {
            $childrenStrings = [];

            foreach ($children as $child) {
                $fio = trim(
                    $child['lastName'] . ' ' .
                    $child['firstName'] . ' ' .
                    ($child['middleName'] ?? '')
                );

                $childrenStrings[] = $fio . ' ' . $child['birthDate'];
            }

            if ($childrenCount === 1) {
                $parts[] = 'имеет на иждивении несовершеннолетнего ребенка: ' . $childrenStrings[0];
            } else {
                $last = array_pop($childrenStrings);
                $parts[] =
                    'имеет на иждивении несовершеннолетних детей: ' .
                    implode(', ', $childrenStrings) .
                    ' и ' . $last;
            }
        }

        return implode(', ', $parts) . '.';
    }
}
