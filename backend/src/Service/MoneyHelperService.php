<?php

declare(strict_types=1);

namespace App\Service;

class MoneyHelperService
{
    private const int KOPECKS_IN_RUBLE = 100;
    private const string THOUSANDS_SEPARATOR = "\u{00A0}";

    public static function format(?string $amount): string
    {
        $kopecks = self::toKopecks(amount: $amount);
        $absolute = abs($kopecks);

        return sprintf(
            '%s%s,%02d',
            $kopecks < 0 ? '-' : '',
            number_format(intdiv($absolute, self::KOPECKS_IN_RUBLE), 0, '', self::THOUSANDS_SEPARATOR),
            $absolute % self::KOPECKS_IN_RUBLE,
        );
    }

    public static function subtract(?string $minuend, ?string $subtrahend): string
    {
        return self::fromKopecks(kopecks: self::toKopecks(amount: $minuend) - self::toKopecks(amount: $subtrahend));
    }

    private static function toKopecks(?string $amount): int
    {
        if ($amount === null || trim($amount) === '') {
            return 0;
        }

        return (int)round((float)str_replace(',', '.', $amount) * self::KOPECKS_IN_RUBLE);
    }

    private static function fromKopecks(int $kopecks): string
    {
        $absolute = abs($kopecks);

        return sprintf(
            '%s%d.%02d',
            $kopecks < 0 ? '-' : '',
            intdiv($absolute, self::KOPECKS_IN_RUBLE),
            $absolute % self::KOPECKS_IN_RUBLE,
        );
    }
}
