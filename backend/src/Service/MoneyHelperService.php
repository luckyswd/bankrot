<?php

declare(strict_types=1);

namespace App\Service;

class MoneyHelperService
{
    private const int KOPECKS_IN_RUBLE = 100;
    private const int PERCENT_SCALE = 10000;
    private const string THOUSANDS_SEPARATOR = "\u{00A0}";
    private const string ZERO = '0.00';
    private const array IGNORED_SEPARATORS = [' ', "\u{00A0}", "\u{202F}"];
    private const string AMOUNT_PATTERN = '/^(-)?(\d+)(?:\.(\d{1,2}))?$/';

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

    public static function formatPlain(?string $amount): string
    {
        return str_replace('.', ',', self::parse(amount: $amount));
    }

    public static function subtract(?string $minuend, ?string $subtrahend): string
    {
        return self::fromKopecks(kopecks: self::toKopecks(amount: $minuend) - self::toKopecks(amount: $subtrahend));
    }

    public static function normalize(?string $amount): ?string
    {
        $kopecks = self::tryToKopecks(amount: $amount);

        return $kopecks === null ? null : self::fromKopecks(kopecks: $kopecks);
    }

    public static function parse(?string $amount): string
    {
        return self::normalize(amount: $amount) ?? self::ZERO;
    }

    public static function add(?string ...$amounts): string
    {
        $total = 0;

        foreach ($amounts as $amount) {
            $total += self::toKopecks(amount: $amount);
        }

        return self::fromKopecks(kopecks: $total);
    }

    public static function min(?string $first, ?string $second): string
    {
        return self::fromKopecks(kopecks: min(self::toKopecks(amount: $first), self::toKopecks(amount: $second)));
    }

    public static function max(?string $first, ?string $second): string
    {
        return self::fromKopecks(kopecks: max(self::toKopecks(amount: $first), self::toKopecks(amount: $second)));
    }

    public static function isPositive(?string $amount): bool
    {
        return self::toKopecks(amount: $amount) > 0;
    }

    public static function percent(?string $part, ?string $total): string
    {
        $partKopecks = self::toKopecks(amount: $part);
        $totalKopecks = self::toKopecks(amount: $total);

        if ($totalKopecks <= 0 || $partKopecks <= 0) {
            return self::formatPlain(amount: self::ZERO);
        }

        $hundredths = intdiv(2 * $partKopecks * self::PERCENT_SCALE + $totalKopecks, 2 * $totalKopecks);

        return self::formatPlain(amount: self::fromKopecks(kopecks: $hundredths));
    }

    private static function toKopecks(?string $amount): int
    {
        return self::tryToKopecks(amount: $amount) ?? 0;
    }

    private static function tryToKopecks(?string $amount): ?int
    {
        if ($amount === null) {
            return null;
        }

        $cleaned = str_replace(',', '.', str_replace(self::IGNORED_SEPARATORS, '', trim($amount)));

        if (preg_match(self::AMOUNT_PATTERN, $cleaned, $matches) !== 1) {
            return null;
        }

        $kopecks = (int)$matches[2] * self::KOPECKS_IN_RUBLE + (int)str_pad($matches[3] ?? '', 2, '0');

        return $matches[1] === '-' ? -$kopecks : $kopecks;
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
