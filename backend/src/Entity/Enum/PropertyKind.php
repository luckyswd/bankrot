<?php

declare(strict_types=1);

namespace App\Entity\Enum;

enum PropertyKind: string
{
    case REAL_ESTATE = 'real_estate';
    case MOVABLE = 'movable';

    public function getLabel(): string
    {
        return match ($this) {
            self::REAL_ESTATE => 'Недвижимое имущество',
            self::MOVABLE => 'Движимое имущество',
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
