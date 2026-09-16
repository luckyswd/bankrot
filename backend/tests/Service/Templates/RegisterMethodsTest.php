<?php

declare(strict_types=1);

namespace App\Tests\Service\Templates;

use App\Entity\ContractsCreditorsClaim;
use App\Entity\Creditor;
use App\Service\Templates\CreditorsRegister\RegisterMethods;
use App\Service\Templates\CreditorsRegister\RegistryClaimRow;
use App\Service\Templates\CreditorsRegister\RegistryPart;
use PHPUnit\Framework\TestCase;

class RegisterMethodsTest extends TestCase
{
    private const string SBERBANK_DETERMINATION = 'Определение Арбитражного суда города Санкт-Петербурга и Ленинградской области от 14.07.2025 г. по делу № А56-117152/2023/тр.1';

    public function testClaimWithoutEntryDateIsNotInRegister(): void
    {
        $contract = RegisterContractFactory::create();
        RegisterContractFactory::claimOf(contract: $contract, creditorName: RegisterContractFactory::VTB)->setRegistryEntryDate(null);

        $rows = RegisterMethods::mainClaims(contract: $contract);

        $this->assertCount(1, $rows);
        $this->assertSame(RegisterContractFactory::SBERBANK, $rows->first()->getCreditorName());
        $this->assertSame(1, $rows->first()->getCreditorNumber());
        $this->assertSame(1, $rows->first()->getClaimNumber());
    }

    public function testNumberingFollowsEntryDateInAllParts(): void
    {
        $contract = RegisterContractFactory::create();

        foreach ([RegisterMethods::mainClaims(contract: $contract), RegisterMethods::sanctionClaims(contract: $contract)] as $rows) {
            $this->assertSame(
                [[RegisterContractFactory::SBERBANK, 1, 1], [RegisterContractFactory::VTB, 2, 2]],
                array_map(
                    static fn (RegistryClaimRow $row): array => [$row->getCreditorName(), $row->getCreditorNumber(), $row->getClaimNumber()],
                    $rows->toArray(),
                ),
            );
        }
    }

    public function testClaimWithBreakdownIsSplitBetweenParts(): void
    {
        $claim = RegisterContractFactory::claimOf(contract: RegisterContractFactory::create(), creditorName: RegisterContractFactory::SBERBANK);

        $this->assertSame('2116862.59', RegisterMethods::mainAmount(claim: $claim));
        $this->assertSame('1206637.98', RegisterMethods::sanctionAmount(claim: $claim));
    }

    public function testOnlyDebtAmountFallsBackToDebtMinusSanctions(): void
    {
        $claim = (new ContractsCreditorsClaim())
            ->setCreditor((new Creditor())->setName('ООО «ФЕНИКС»'))
            ->setDebtAmount('363 300,48')
            ->setLateFee('63 300,48');

        $this->assertSame('300000.00', RegisterMethods::mainAmount(claim: $claim));
        $this->assertSame('63300.48', RegisterMethods::sanctionAmount(claim: $claim));
    }

    public function testDebtAmountSmallerThanSanctionsGivesZeroMainAmount(): void
    {
        $claim = (new ContractsCreditorsClaim())
            ->setCreditor((new Creditor())->setName('ООО «ФЕНИКС»'))
            ->setDebtAmount('100,00')
            ->setPenalty('150,00');

        $this->assertSame('0.00', RegisterMethods::mainAmount(claim: $claim));
    }

    public function testClaimWithoutSanctionsIsOnlyInMainPart(): void
    {
        $contract = RegisterContractFactory::create();
        RegisterContractFactory::claimOf(contract: $contract, creditorName: RegisterContractFactory::VTB)->setLateFee(null);

        $this->assertCount(2, RegisterMethods::mainClaims(contract: $contract));
        $sanctionRows = RegisterMethods::sanctionClaims(contract: $contract);
        $this->assertCount(1, $sanctionRows);
        $this->assertSame(RegisterContractFactory::SBERBANK, $sanctionRows->first()->getCreditorName());
    }

    public function testRepaymentOverMainAmountGoesToSanctions(): void
    {
        $claim = RegisterContractFactory::claimOf(contract: RegisterContractFactory::create(), creditorName: RegisterContractFactory::SBERBANK)
            ->setRepaidAmount('2200000.00');

        $this->assertSame('2116862.59', RegisterMethods::repaidAmount(claim: $claim, part: RegistryPart::MAIN));
        $this->assertSame('83137.41', RegisterMethods::repaidAmount(claim: $claim, part: RegistryPart::SANCTION));
    }

    public function testPartialRepaymentStaysInMainPart(): void
    {
        $claim = RegisterContractFactory::claimOf(contract: RegisterContractFactory::create(), creditorName: RegisterContractFactory::SBERBANK)
            ->setRepaidAmount('100000.00');

        $this->assertSame('100000.00', RegisterMethods::repaidAmount(claim: $claim, part: RegistryPart::MAIN));
        $this->assertSame('0.00', RegisterMethods::repaidAmount(claim: $claim, part: RegistryPart::SANCTION));
    }

    public function testMainRowTexts(): void
    {
        $row = RegisterMethods::mainClaims(contract: RegisterContractFactory::create())->first();

        $this->assertSame('21.07.2025', $row->getEntryDate());
        $this->assertSame('117997, Г. МОСКВА, УЛ. ВАВИЛОВА, Д. 19', $row->getCreditorAddress());
        $this->assertSame('ГРЕФ ГЕРМАН ОСКАРОВИЧ', $row->getCreditorHeadFullName());
        $this->assertSame('БИК 044525225, ИНН 7707083893, КПП 773601001', $row->getCreditorBankDetails());
        $this->assertSame('Кредит', $row->getKind());
        $this->assertSame('Кредитный договор № 93-12345 от 15.03.2019 г.', $row->getBasisText());
        $this->assertSame('15.03.2019', $row->getOriginDate());
        $this->assertSame('2116862,59', $row->getAmountText());
        $this->assertSame(self::SBERBANK_DETERMINATION, $row->getDeterminationText());
    }

    public function testVtbRowWithTwoBasesAndCaseNumber(): void
    {
        $row = RegisterMethods::mainClaims(contract: RegisterContractFactory::create())->get(1);

        $this->assertNotNull($row);
        $this->assertSame('29.07.2025', $row->getEntryDate());
        $this->assertSame('Кредитные договоры № 625/0055-123 от 01.02.2020 г., № 633/0055-456 от 03.04.2021 г.', $row->getBasisText());
        $this->assertSame('01.02.2020', $row->getOriginDate());
        $this->assertSame('1500000,00', $row->getAmountText());
        $this->assertSame('Определение Арбитражного суда города Санкт-Петербурга и Ленинградской области от 25.07.2025 г. по делу № А56-117152/2023', $row->getDeterminationText());
    }

    public function testSanctionRowsKindAndAmount(): void
    {
        $rows = RegisterMethods::sanctionClaims(contract: RegisterContractFactory::create());

        $this->assertSame(
            [['Штраф', '1206637,98'], ['Пени', '1000000,00']],
            array_map(static fn (RegistryClaimRow $row): array => [$row->getKind(), $row->getAmountText()], $rows->toArray()),
        );
    }

    public function testAllSanctionKindsAreListedInOrder(): void
    {
        $contract = RegisterContractFactory::create();
        RegisterContractFactory::claimOf(contract: $contract, creditorName: RegisterContractFactory::SBERBANK)
            ->setLateFee('10,00')
            ->setForfeiture('20,00');

        $this->assertSame('Штраф, пени, неустойка', RegisterMethods::sanctionClaims(contract: $contract)->first()->getKind());
    }

    public function testCreditCardIsAddedToBasis(): void
    {
        $contract = RegisterContractFactory::create();
        RegisterContractFactory::claimOf(contract: $contract, creditorName: RegisterContractFactory::SBERBANK)
            ->setIsCreditCard(true)
            ->setCreditCardDate(new \DateTime('2019-09-06'));

        $this->assertSame(
            'Кредитный договор № 93-12345 от 15.03.2019 г. и договор обслуживания кредитной карты от 06.09.2019 г.',
            RegisterMethods::mainClaims(contract: $contract)->first()->getBasisText(),
        );
    }

    public function testCreditCardWithoutBasis(): void
    {
        $contract = RegisterContractFactory::create();
        RegisterContractFactory::claimOf(contract: $contract, creditorName: RegisterContractFactory::SBERBANK)
            ->setBasis(null)
            ->setIsCreditCard(true)
            ->setCreditCardDate(new \DateTime('2019-09-06'));

        $this->assertSame(
            'Договор обслуживания кредитной карты от 06.09.2019 г.',
            RegisterMethods::mainClaims(contract: $contract)->first()->getBasisText(),
        );
    }

    public function testDeterminationWithoutActDate(): void
    {
        $contract = RegisterContractFactory::create();
        RegisterContractFactory::claimOf(contract: $contract, creditorName: RegisterContractFactory::SBERBANK)->setJudicialActDate(null);

        $this->assertSame(
            'Определение Арбитражного суда города Санкт-Петербурга и Ленинградской области по делу № А56-117152/2023/тр.1',
            RegisterMethods::mainClaims(contract: $contract)->first()->getDeterminationText(),
        );
    }

    public function testTotalsForExample(): void
    {
        $contract = RegisterContractFactory::create();

        $this->assertSame(
            [2, 2, '3616862,59', '0,00', '0,00'],
            [
                RegisterMethods::mainCreditorsCount(contract: $contract),
                RegisterMethods::mainClaimsCount(contract: $contract),
                RegisterMethods::mainAmountText(contract: $contract),
                RegisterMethods::mainRepaidAmountText(contract: $contract),
                RegisterMethods::mainRepaidPercentText(contract: $contract),
            ],
        );
        $this->assertSame(
            [2, 2, '2206637,98', '0,00', '0,00'],
            [
                RegisterMethods::sanctionCreditorsCount(contract: $contract),
                RegisterMethods::sanctionClaimsCount(contract: $contract),
                RegisterMethods::sanctionAmountText(contract: $contract),
                RegisterMethods::sanctionRepaidAmountText(contract: $contract),
                RegisterMethods::sanctionRepaidPercentText(contract: $contract),
            ],
        );
    }

    public function testTotalsWithRepayment(): void
    {
        $contract = RegisterContractFactory::create();
        RegisterContractFactory::claimOf(contract: $contract, creditorName: RegisterContractFactory::SBERBANK)->setRepaidAmount('2200000.00');

        $this->assertSame('2116862,59', RegisterMethods::mainRepaidAmountText(contract: $contract));
        $this->assertSame('58,53', RegisterMethods::mainRepaidPercentText(contract: $contract));
        $this->assertSame('83137,41', RegisterMethods::sanctionRepaidAmountText(contract: $contract));
        $this->assertSame('3,77', RegisterMethods::sanctionRepaidPercentText(contract: $contract));
    }

    public function testEmptyRegister(): void
    {
        $contract = RegisterContractFactory::create();

        foreach ($contract->getCreditorsClaims() as $claim) {
            $claim->setRegistryEntryDate(null);
        }

        $this->assertCount(0, RegisterMethods::mainClaims(contract: $contract));
        $this->assertCount(0, RegisterMethods::sanctionClaims(contract: $contract));
        $this->assertSame(0, RegisterMethods::mainCreditorsCount(contract: $contract));
        $this->assertSame(0, RegisterMethods::sanctionClaimsCount(contract: $contract));
        $this->assertSame('0,00', RegisterMethods::mainAmountText(contract: $contract));
        $this->assertSame('0,00', RegisterMethods::sanctionRepaidPercentText(contract: $contract));
    }

    public function testOpeningAndClosingDates(): void
    {
        $contract = RegisterContractFactory::create();

        $this->assertSame('17.05.2025 г.', RegisterMethods::openingDateShort(contract: $contract));
        $this->assertSame('17.07.2025 г.', RegisterMethods::closingDateShort(contract: $contract));

        $contract->setProcedureInitiationKommersantPublicationDate(null);

        $this->assertSame('', RegisterMethods::openingDateShort(contract: $contract));
        $this->assertSame('', RegisterMethods::closingDateShort(contract: $contract));
    }

    public function testRegistrationAddressWithPostalCode(): void
    {
        $contract = RegisterContractFactory::create();

        $this->assertSame('196233, г. Санкт-Петербург, ул. Савушкина, д. 18, кв. 18', RegisterMethods::registrationAddressWithPostalCode(contract: $contract));

        $contract->setPostalCode(null);

        $this->assertSame('г. Санкт-Петербург, ул. Савушкина, д. 18, кв. 18', RegisterMethods::registrationAddressWithPostalCode(contract: $contract));
    }
}
