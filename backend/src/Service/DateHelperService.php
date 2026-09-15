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

    public static function formatGenitive(?\DateTimeInterface $date): string
    {
        if ($date === null) {
            return '';
        }

        return sprintf(
            '«%s» %s %s г.',
            $date->format('d'),
            self::getMonthNameGenitive(monthNumber: $date->format('n')),
            $date->format('Y'),
        );
    }

    public static function addMonthsKeepingMonthEnd(\DateTimeInterface $date, int $months): \DateTimeImmutable
    {
        $source = \DateTimeImmutable::createFromInterface($date)->setTime(hour: 0, minute: 0);
        $targetMonth = $source->modify('first day of this month')->modify(sprintf('%+d months', $months));
        $day = min((int)$source->format('j'), (int)$targetMonth->format('t'));

        return $targetMonth->setDate(
            year: (int)$targetMonth->format('Y'),
            month: (int)$targetMonth->format('n'),
            day: $day,
        );
    }
}
