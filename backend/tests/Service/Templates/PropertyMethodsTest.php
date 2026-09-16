<?php

declare(strict_types=1);

namespace App\Tests\Service\Templates;

use App\Entity\Contracts;
use App\Entity\ContractsProperty;
use App\Entity\Enum\PropertyKind;
use App\Entity\Enum\PropertySubtype;
use App\Service\Templates\Property\PropertyMethods;
use App\Service\Templates\Property\PropertyRow;
use PHPUnit\Framework\TestCase;

class PropertyMethodsTest extends TestCase
{
    public function testInventoryRowsRepeatFormWhenPropertyIsMissing(): void
    {
        $rows = PropertyMethods::inventoryRows(contract: new Contracts(), kind: PropertyKind::REAL_ESTATE);

        $this->assertCount(5, $rows);
        $this->assertSame('Земельные участки:', $rows->first()->getName());
        $this->assertSame('—', $rows->first()->getOwnershipType());
        $this->assertSame('—', $rows->first()->getArea());
    }

    public function testInventoryRowsListPropertyWithSubtypeLabel(): void
    {
        $rows = PropertyMethods::inventoryRows(contract: $this->contract(), kind: PropertyKind::REAL_ESTATE);

        $this->assertCount(1, $rows);
        $row = $rows->first();

        $this->assertSame('Квартиры: квартира в многоквартирном доме', $row->getName());
        $this->assertSame('общая долевая собственность, доля в праве ½', $row->getOwnershipType());
        $this->assertSame('г. Санкт-Петербург, ул. Савушкина, д. 18, кв. 18', $row->getLocation());
        $this->assertSame('78,8', $row->getArea());
        $this->assertSame('—', $row->getPledgeInfo());
    }

    public function testMovableInventoryRowKeepsIdentificationNumber(): void
    {
        $rows = PropertyMethods::inventoryRows(contract: $this->contract(), kind: PropertyKind::MOVABLE);
        $row = $rows->first();

        $this->assertSame('Автомобили легковые: LADA GRANTA, 2019 г. в.', $row->getName());
        $this->assertSame('XTA219010K0512345', $row->getIdentificationNumber());
    }

    public function testReportRowsCarryValuations(): void
    {
        $rows = PropertyMethods::reportRows(contract: $this->contract(), kind: PropertyKind::REAL_ESTATE);
        $row = $rows->first();

        $this->assertInstanceOf(PropertyRow::class, $row);
        $this->assertSame('квартира в многоквартирном доме', $row->getName());
        $this->assertSame('1000000,00', $row->getManagerValuation());
        $this->assertSame('1200000,00', $row->getAppraiserValuation());
        $this->assertSame('1000000,00, единственное пригодное для проживания жильё', $row->getExclusionText());
    }

    public function testReportRowsFallBackToDashes(): void
    {
        $rows = PropertyMethods::reportRows(contract: new Contracts(), kind: PropertyKind::MOVABLE);

        $this->assertCount(1, $rows);
        $this->assertSame('–', $rows->first()->getName());
        $this->assertSame('–', $rows->first()->getManagerValuation());
    }

    public function testTotals(): void
    {
        $contract = $this->contract();

        $this->assertSame('1000000,00', PropertyMethods::managerTotal(contract: $contract, kind: PropertyKind::REAL_ESTATE));
        $this->assertSame('253453,82', PropertyMethods::managerTotal(contract: $contract, kind: PropertyKind::MOVABLE));
        $this->assertSame('1253453,82', PropertyMethods::managerTotal(contract: $contract));
        $this->assertSame('1200000,00', PropertyMethods::appraiserTotal(contract: $contract));
        $this->assertSame('1000000,00', PropertyMethods::excludedTotal(contract: $contract));
    }

    public function testTotalsAreZeroWithoutProperty(): void
    {
        $contract = new Contracts();

        $this->assertSame('0,00', PropertyMethods::managerTotal(contract: $contract));
        $this->assertSame('0,00', PropertyMethods::appraiserTotal(contract: $contract, kind: PropertyKind::REAL_ESTATE));
        $this->assertSame('0,00', PropertyMethods::excludedTotal(contract: $contract, kind: PropertyKind::MOVABLE));
    }

    public function testSummaryText(): void
    {
        $contract = $this->contract();

        $this->assertSame(
            'квартира в многоквартирном доме, г. Санкт-Петербург, ул. Савушкина, д. 18, кв. 18',
            PropertyMethods::summaryText(contract: $contract, kind: PropertyKind::REAL_ESTATE),
        );
        $this->assertSame('не выявлено', PropertyMethods::summaryText(contract: new Contracts(), kind: PropertyKind::REAL_ESTATE));
    }

    private function contract(): Contracts
    {
        $apartment = (new ContractsProperty())
            ->setSubtype(PropertySubtype::APARTMENT)
            ->setName('квартира в многоквартирном доме')
            ->setOwnershipType('общая долевая собственность, доля в праве ½')
            ->setLocation('г. Санкт-Петербург, ул. Савушкина, д. 18, кв. 18')
            ->setArea('78,8')
            ->setManagerValuation('1 000 000,00')
            ->setAppraiserValuation('1 200 000,00')
            ->setIsExcludedFromEstate(true)
            ->setExclusionReason('единственное пригодное для проживания жильё')
            ->setExcludedValuation('1 000 000,00');

        $car = (new ContractsProperty())
            ->setSubtype(PropertySubtype::CAR)
            ->setName('LADA GRANTA, 2019 г. в.')
            ->setIdentificationNumber('XTA219010K0512345')
            ->setManagerValuation('253 453,82');

        return (new Contracts())
            ->addProperty($apartment)
            ->addProperty($car);
    }
}
