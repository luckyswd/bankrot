<?php

declare(strict_types=1);

namespace App\Tests\Entity\Enum;

use App\Entity\Enum\PropertyKind;
use App\Entity\Enum\PropertySubtype;
use PHPUnit\Framework\TestCase;

class PropertySubtypeTest extends TestCase
{
    public function testRealEstateSubtypesFollowInventoryForm(): void
    {
        $labels = array_map(
            static fn (PropertySubtype $subtype): string => $subtype->getLabel(),
            PropertyKind::REAL_ESTATE->getSubtypes(),
        );

        $this->assertSame(
            ['Земельные участки', 'Жилые дома, дачи', 'Квартиры', 'Гаражи', 'Иное недвижимое имущество'],
            $labels,
        );
    }

    public function testMovableSubtypesFollowInventoryForm(): void
    {
        $labels = array_map(
            static fn (PropertySubtype $subtype): string => $subtype->getLabel(),
            PropertyKind::MOVABLE->getSubtypes(),
        );

        $this->assertSame(
            [
                'Автомобили легковые',
                'Автомобили грузовые',
                'Мототранспортные средства',
                'Сельскохозяйственная техника',
                'Водный транспорт',
                'Воздушный транспорт',
                'Иные транспортные средства',
            ],
            $labels,
        );
    }

    public function testEverySubtypeBelongsToSingleKind(): void
    {
        $subtypes = array_merge(PropertyKind::REAL_ESTATE->getSubtypes(), PropertyKind::MOVABLE->getSubtypes());

        $this->assertCount(count(PropertySubtype::cases()), $subtypes);
        $this->assertSame(PropertyKind::REAL_ESTATE, PropertySubtype::APARTMENT->getKind());
        $this->assertSame(PropertyKind::MOVABLE, PropertySubtype::CAR->getKind());
    }
}
