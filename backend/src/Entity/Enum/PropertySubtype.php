<?php

declare(strict_types=1);

namespace App\Entity\Enum;

enum PropertySubtype: string
{
    case LAND_PLOT = 'land_plot';
    case HOUSE = 'house';
    case APARTMENT = 'apartment';
    case GARAGE = 'garage';
    case OTHER_REAL_ESTATE = 'other_real_estate';
    case CAR = 'car';
    case TRUCK = 'truck';
    case MOTORCYCLE = 'motorcycle';
    case AGRICULTURAL_MACHINERY = 'agricultural_machinery';
    case WATER_TRANSPORT = 'water_transport';
    case AIR_TRANSPORT = 'air_transport';
    case OTHER_VEHICLE = 'other_vehicle';

    public function getKind(): PropertyKind
    {
        return match ($this) {
            self::LAND_PLOT,
            self::HOUSE,
            self::APARTMENT,
            self::GARAGE,
            self::OTHER_REAL_ESTATE => PropertyKind::REAL_ESTATE,
            self::CAR,
            self::TRUCK,
            self::MOTORCYCLE,
            self::AGRICULTURAL_MACHINERY,
            self::WATER_TRANSPORT,
            self::AIR_TRANSPORT,
            self::OTHER_VEHICLE => PropertyKind::MOVABLE,
        };
    }

    public function getLabel(): string
    {
        return match ($this) {
            self::LAND_PLOT => 'Земельные участки',
            self::HOUSE => 'Жилые дома, дачи',
            self::APARTMENT => 'Квартиры',
            self::GARAGE => 'Гаражи',
            self::OTHER_REAL_ESTATE => 'Иное недвижимое имущество',
            self::CAR => 'Автомобили легковые',
            self::TRUCK => 'Автомобили грузовые',
            self::MOTORCYCLE => 'Мототранспортные средства',
            self::AGRICULTURAL_MACHINERY => 'Сельскохозяйственная техника',
            self::WATER_TRANSPORT => 'Водный транспорт',
            self::AIR_TRANSPORT => 'Воздушный транспорт',
            self::OTHER_VEHICLE => 'Иные транспортные средства',
        };
    }

    /**
     * @return array<int, string>
     */
    public static function getValues(): array
    {
        return array_column(self::cases(), 'value');
    }
}
