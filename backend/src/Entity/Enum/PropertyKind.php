<?php

declare(strict_types=1);

namespace App\Entity\Enum;

enum PropertyKind: string
{
    case REAL_ESTATE = 'real_estate';
    case MOVABLE = 'movable';
    case BANK_ACCOUNT = 'bank_account';
    case CASH = 'cash';
    case SHARES = 'shares';
    case SECURITIES = 'securities';
    case VALUABLES = 'valuables';
    case RECEIVABLES = 'receivables';
    case EXCLUSIVE_RIGHTS = 'exclusive_rights';

    public function getLabel(): string
    {
        return match ($this) {
            self::REAL_ESTATE => 'Недвижимое имущество',
            self::MOVABLE => 'Движимое имущество',
            self::BANK_ACCOUNT => 'Денежные средства на счетах',
            self::CASH => 'Наличные денежные средства',
            self::SHARES => 'Акции и иное участие в коммерческих организациях',
            self::SECURITIES => 'Ценные бумаги',
            self::VALUABLES => 'Ценное имущество',
            self::RECEIVABLES => 'Дебиторская задолженность',
            self::EXCLUSIVE_RIGHTS => 'Исключительные права',
        };
    }

    public function hasSubtypeLabels(): bool
    {
        return match ($this) {
            self::REAL_ESTATE, self::MOVABLE, self::VALUABLES => true,
            default => false,
        };
    }

    /**
     * @return array<int, PropertySubtype>
     */
    public function getSubtypes(): array
    {
        return array_values(array_filter(
            PropertySubtype::cases(),
            fn (PropertySubtype $subtype): bool => $subtype->getKind() === $this,
        ));
    }

    /**
     * @return array<int, string>
     */
    public static function getValues(): array
    {
        return array_column(self::cases(), 'value');
    }
}
