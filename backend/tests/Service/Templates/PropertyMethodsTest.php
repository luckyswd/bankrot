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
        $rows = PropertyMethods::reportRows(contract: $this->contract(), kinds: [PropertyKind::REAL_ESTATE]);
        $row = $rows->first();

        $this->assertInstanceOf(PropertyRow::class, $row);
        $this->assertSame('квартира в многоквартирном доме', $row->getName());
        $this->assertSame('1000000,00', $row->getManagerValuation());
        $this->assertSame('1200000,00', $row->getAppraiserValuation());
        $this->assertSame('1000000,00, единственное пригодное для проживания жильё', $row->getExclusionText());
    }

    public function testReportRowsFallBackToDashes(): void
    {
        $rows = PropertyMethods::reportRows(contract: new Contracts(), kinds: [PropertyKind::MOVABLE]);

        $this->assertCount(1, $rows);
        $this->assertSame('–', $rows->first()->getName());
        $this->assertSame('–', $rows->first()->getManagerValuation());
    }

    public function testTotals(): void
    {
        $contract = $this->contract();

        $this->assertSame('1000000,00', PropertyMethods::managerTotal(contract: $contract, kinds: [PropertyKind::REAL_ESTATE]));
        $this->assertSame('253453,82', PropertyMethods::managerTotal(contract: $contract, kinds: [PropertyKind::MOVABLE]));
        $this->assertSame('1253453,82', PropertyMethods::managerTotal(contract: $contract));
        $this->assertSame('1200000,00', PropertyMethods::appraiserTotal(contract: $contract));
        $this->assertSame('1000000,00', PropertyMethods::excludedTotal(contract: $contract));
    }

    public function testTotalsAreZeroWithoutProperty(): void
    {
        $contract = new Contracts();

        $this->assertSame('0,00', PropertyMethods::managerTotal(contract: $contract));
        $this->assertSame('0,00', PropertyMethods::appraiserTotal(contract: $contract, kinds: [PropertyKind::REAL_ESTATE]));
        $this->assertSame('0,00', PropertyMethods::excludedTotal(contract: $contract, kinds: [PropertyKind::MOVABLE]));
    }

    public function testSummaryText(): void
    {
        $contract = $this->contract();

        $this->assertSame(
            'квартира в многоквартирном доме, г. Санкт-Петербург, ул. Савушкина, д. 18, кв. 18',
            PropertyMethods::summaryText(contract: $contract, kinds: [PropertyKind::REAL_ESTATE]),
        );
        $this->assertSame('не выявлено', PropertyMethods::summaryText(contract: new Contracts(), kinds: [PropertyKind::REAL_ESTATE]));
    }

    public function testBankAccountRowFollowsInventoryForm(): void
    {
        $rows = PropertyMethods::inventoryRows(contract: $this->categoriesContract(), kind: PropertyKind::BANK_ACCOUNT);
        $row = $rows->first();

        $this->assertCount(1, $rows);
        $this->assertSame('ПАО «Сбербанк России»', $row->getName());
        $this->assertSame('текущий, рубли', $row->getAccountType());
        $this->assertSame('12.03.2019', $row->getOpenedAt());
        $this->assertSame('50000,00', $row->getAmount());
    }

    public function testValuablesRowKeepsSubtypeLabel(): void
    {
        $rows = PropertyMethods::inventoryRows(contract: $this->categoriesContract(), kind: PropertyKind::VALUABLES);

        $this->assertSame('Предметы искусства: картина «Рассвет»', $rows->first()->getName());
        $this->assertSame('сейф в банке', $rows->first()->getLocation());
    }

    public function testReceivablesRow(): void
    {
        $rows = PropertyMethods::inventoryRows(contract: $this->categoriesContract(), kind: PropertyKind::RECEIVABLES);
        $row = $rows->first();

        $this->assertSame('Иванов Иван Иванович', $row->getName());
        $this->assertSame('30000,00', $row->getAmount());
        $this->assertSame('заём по расписке', $row->getObligationContent());
        $this->assertSame('расписка от 01.02.2020 г.', $row->getBasisText());
    }

    public function testCategoriesWithoutRecordsGiveSingleDashRow(): void
    {
        foreach ([
            PropertyKind::BANK_ACCOUNT,
            PropertyKind::CASH,
            PropertyKind::SHARES,
            PropertyKind::SECURITIES,
            PropertyKind::RECEIVABLES,
            PropertyKind::EXCLUSIVE_RIGHTS,
        ] as $kind) {
            $rows = PropertyMethods::inventoryRows(contract: new Contracts(), kind: $kind);

            $this->assertCount(1, $rows, $kind->value);
            $this->assertSame('—', $rows->first()->getName(), $kind->value);
            $this->assertSame('—', $rows->first()->getAmount(), $kind->value);
        }

        $this->assertCount(4, PropertyMethods::inventoryRows(contract: new Contracts(), kind: PropertyKind::VALUABLES));
    }

    public function testMoneyGroupTotalCombinesAccountsAndCash(): void
    {
        $contract = $this->categoriesContract();

        $this->assertSame(
            '70000,00',
            PropertyMethods::managerTotal(contract: $contract, kinds: [PropertyKind::BANK_ACCOUNT, PropertyKind::CASH]),
        );
        $this->assertSame('105000,00', PropertyMethods::managerTotal(contract: $contract));
    }

    public function testSummaryWordingDependsOnCategory(): void
    {
        $contract = new Contracts();

        $this->assertSame(
            'не выявлены',
            PropertyMethods::summaryText(contract: $contract, kinds: [PropertyKind::BANK_ACCOUNT], notFound: PropertyMethods::NOT_FOUND_PLURAL),
        );
        $this->assertSame(
            'не выявлена',
            PropertyMethods::summaryText(contract: $contract, kinds: [PropertyKind::RECEIVABLES], notFound: PropertyMethods::NOT_FOUND_FEMININE),
        );
        $this->assertSame(
            'ПАО «Сбербанк России»',
            PropertyMethods::summaryText(contract: $this->categoriesContract(), kinds: [PropertyKind::BANK_ACCOUNT]),
        );
    }

    private function categoriesContract(): Contracts
    {
        $account = (new ContractsProperty())
            ->setSubtype(PropertySubtype::BANK_ACCOUNT)
            ->setName('ПАО «Сбербанк России»')
            ->setAccountType('текущий, рубли')
            ->setOpenedAt(new \DateTime('2019-03-12'))
            ->setAmount('50 000,00')
            ->setManagerValuation('50 000,00');

        $cash = (new ContractsProperty())
            ->setSubtype(PropertySubtype::CASH)
            ->setName('наличные денежные средства')
            ->setAmount('20 000,00')
            ->setCurrency('рубли')
            ->setManagerValuation('20 000,00');

        $art = (new ContractsProperty())
            ->setSubtype(PropertySubtype::ART)
            ->setName('картина «Рассвет»')
            ->setLocation('сейф в банке')
            ->setManagerValuation('5 000,00');

        $receivable = (new ContractsProperty())
            ->setSubtype(PropertySubtype::RECEIVABLES)
            ->setName('Иванов Иван Иванович')
            ->setAmount('30 000,00')
            ->setObligationContent('заём по расписке')
            ->setBasisText('расписка от 01.02.2020 г.')
            ->setManagerValuation('30 000,00');

        return (new Contracts())
            ->addProperty($account)
            ->addProperty($cash)
            ->addProperty($art)
            ->addProperty($receivable);
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
