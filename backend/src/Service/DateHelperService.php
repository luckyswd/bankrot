<?php

declare(strict_types=1);

namespace App\Service;

class DateHelperService
{
    /**
     * Получить название месяца в родительном падеже по номеру месяца.
     *
     * @param string $monthNumber Номер месяца (1-12)
     *
     * @return string Название месяца в родительном падеже
     */
    public static function getMonthNameGenitive(string $monthNumber): string
    {
        $months = [
            '1' => 'января',
            '2' => 'февраля',
            '3' => 'марта',
            '4' => 'апреля',
            '5' => 'мая',
            '6' => 'июня',
            '7' => 'июля',
            '8' => 'августа',
            '9' => 'сентября',
            '10' => 'октября',
            '11' => 'ноября',
            '12' => 'декабря',
        ];

        return $months[$monthNumber] ?? '';
    }
}
