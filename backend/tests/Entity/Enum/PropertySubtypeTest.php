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

    public function testValuablesSubtypesFollowInventoryForm(): void
    {
        $labels = array_map(
            static fn (PropertySubtype $subtype): string => $subtype->getLabel(),
            PropertyKind::VALUABLES->getSubtypes(),
        );

        $this->assertSame(
            [
                'Драгоценности, в том числе ювелирные украшения, и другие предметы роскоши',
                'Предметы искусства',
                'Имущество, необходимое для профессиональных занятий',
                'Иное ценное имущество',
            ],
            $labels,
        );
    }

    public function testKindsWithoutSubtypeLabelsHaveSingleSubtype(): void
    {
        foreach ([
            PropertyKind::BANK_ACCOUNT,
            PropertyKind::CASH,
            PropertyKind::SHARES,
            PropertyKind::SECURITIES,
            PropertyKind::RECEIVABLES,
            PropertyKind::EXCLUSIVE_RIGHTS,
        ] as $kind) {
            $this->assertFalse($kind->hasSubtypeLabels(), $kind->value);
            $this->assertCount(1, $kind->getSubtypes(), $kind->value);
        }

        $this->assertTrue(PropertyKind::REAL_ESTATE->hasSubtypeLabels());
        $this->assertTrue(PropertyKind::MOVABLE->hasSubtypeLabels());
        $this->assertTrue(PropertyKind::VALUABLES->hasSubtypeLabels());
    }

    public function testEverySubtypeBelongsToSingleKind(): void
    {
        $subtypes = [];

        foreach (PropertyKind::cases() as $kind) {
            $subtypes = array_merge($subtypes, $kind->getSubtypes());
        }

        $this->assertCount(count(PropertySubtype::cases()), $subtypes);
        $this->assertSame(PropertyKind::REAL_ESTATE, PropertySubtype::APARTMENT->getKind());
        $this->assertSame(PropertyKind::MOVABLE, PropertySubtype::CAR->getKind());
        $this->assertSame(PropertyKind::VALUABLES, PropertySubtype::ART->getKind());
    }
}
