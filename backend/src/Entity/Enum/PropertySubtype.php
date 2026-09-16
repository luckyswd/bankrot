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
    case BANK_ACCOUNT = 'bank_account';
    case CASH = 'cash';
    case SHARES = 'shares';
    case SECURITIES = 'securities';
    case JEWELRY = 'jewelry';
    case ART = 'art';
    case PROFESSIONAL_EQUIPMENT = 'professional_equipment';
    case OTHER_VALUABLES = 'other_valuables';
    case RECEIVABLES = 'receivables';
    case EXCLUSIVE_RIGHTS = 'exclusive_rights';

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
            self::BANK_ACCOUNT => PropertyKind::BANK_ACCOUNT,
            self::CASH => PropertyKind::CASH,
            self::SHARES => PropertyKind::SHARES,
            self::SECURITIES => PropertyKind::SECURITIES,
            self::JEWELRY,
            self::ART,
            self::PROFESSIONAL_EQUIPMENT,
            self::OTHER_VALUABLES => PropertyKind::VALUABLES,
            self::RECEIVABLES => PropertyKind::RECEIVABLES,
            self::EXCLUSIVE_RIGHTS => PropertyKind::EXCLUSIVE_RIGHTS,
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
            self::JEWELRY => 'Драгоценности, в том числе ювелирные украшения, и другие предметы роскоши',
            self::ART => 'Предметы искусства',
            self::PROFESSIONAL_EQUIPMENT => 'Имущество, необходимое для профессиональных занятий',
            self::OTHER_VALUABLES => 'Иное ценное имущество',
            self::BANK_ACCOUNT,
            self::CASH,
            self::SHARES,
            self::SECURITIES,
            self::RECEIVABLES,
            self::EXCLUSIVE_RIGHTS => $this->getKind()->getLabel(),
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
