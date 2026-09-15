<?php

declare(strict_types=1);

namespace App\Tests\Service\Templates;

use App\Entity\Contracts;
use App\Entity\ContractsCreditorsClaim;
use App\Entity\ContractsPreCourtCreditor;
use App\Entity\Creditor;
use App\Entity\Enum\ProcedureExtensionStatus;
use App\Service\Templates\JudicialReport\ReportMethods;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

class ReportMethodsTest extends TestCase
{
    /**
     * @return array<string, array{string, string, string}>
     */
    public static function spouseVariants(): array
    {
        return [
            'должница в браке' => ['female', 'married', 'за должником и его супругом автотранспортных средствах'],
            'должник в браке' => ['male', 'married', 'за должником и его супругой автотранспортных средствах'],
            'должница, брак расторгнут' => ['female', 'married_3y_ago', 'за должником и его бывшим супругом автотранспортных средствах'],
            'должник, брак расторгнут' => ['male', 'married_3y_ago', 'за должником и его бывшей супругой автотранспортных средствах'],
            'должница не в браке' => ['female', 'single', 'за должником автотранспортных средствах'],
        ];
    }

    #[DataProvider('spouseVariants')]
    public function testSpouseInstrumentalInSentence(string $gender, string $maritalStatus, string $expectedSentence): void
    {
        $contract = (new Contracts())->setGender($gender)->setMaritalStatus($maritalStatus);

        $sentence = 'за должником' . ReportMethods::spouseInstrumental(contract: $contract) . ' автотранспортных средствах';

        $this->assertSame($expectedSentence, $sentence);
    }

    /**
     * @return array<string, array{ProcedureExtensionStatus|null, array<int, string>, string}>
     */
    public static function extensionVariants(): array
    {
        return [
            'продлевалась дважды' => [ProcedureExtensionStatus::EXTENDED, ['2026-03-12', '2026-09-10'], '12.03.2026 г., 10.09.2026 г.'],
            'не продлевалась' => [ProcedureExtensionStatus::NOT_EXTENDED, [], 'Процедура реализации имущества не продлевалась'],
            'акты не выносились' => [ProcedureExtensionStatus::NO_ACTS, [], 'Судебные акты не выносились'],
            'не выбрано' => [null, [], ''],
        ];
    }

    /**
     * @param array<int, string> $dates
     */
    #[DataProvider('extensionVariants')]
    public function testProcedureExtensionText(?ProcedureExtensionStatus $status, array $dates, string $expected): void
    {
        $contract = (new Contracts())->changeProcedureExtension(status: $status, dates: $dates);

        $this->assertSame($expected, ReportMethods::procedureExtensionText(contract: $contract));
    }

    public function testZagsCertificatePeriodWithBothDates(): void
    {
        $contract = (new Contracts())
            ->setZagsCertificatePeriodFrom(new \DateTime('2009-02-11'))
            ->setZagsCertificatePeriodTo(new \DateTime('2025-07-10'));

        $this->assertSame('«11» февраля 2009 г. по «10» июля 2025 г.', ReportMethods::zagsCertificatePeriodText(contract: $contract));
    }

    public function testZagsCertificatePeriodWithoutEndDate(): void
    {
        $contract = (new Contracts())->setZagsCertificatePeriodFrom(new \DateTime('2009-02-11'));

        $this->assertSame('«11» февраля 2009 г.', ReportMethods::zagsCertificatePeriodText(contract: $contract));
    }

    public function testZagsCertificatePeriodIsEmptyWithoutDates(): void
    {
        $this->assertSame('', ReportMethods::zagsCertificatePeriodText(contract: new Contracts()));
    }

    public function testDefaultZagsDepartment(): void
    {
        $this->assertSame(
            'отдел ЗАГС Комитета по делам ЗАГС Правительства Санкт-Петербурга',
            ReportMethods::zagsDepartmentText(contract: (new Contracts())->setZagsDepartment('  ')),
        );
    }

    public function testCustomZagsDepartment(): void
    {
        $contract = (new Contracts())->setZagsDepartment('Отдел ЗАГС администрации Всеволожского муниципального района Ленинградской области');

        $this->assertSame(
            'Отдел ЗАГС администрации Всеволожского муниципального района Ленинградской области',
            ReportMethods::zagsDepartmentText(contract: $contract),
        );
    }

    public function testPreviousFullNameWhenLastNameNotChanged(): void
    {
        $contract = (new Contracts())->setIsLastNameChanged(false)->setChangedLastName('Петрова Мария Петровна');

        $this->assertSame('–', ReportMethods::previousFullName(contract: $contract));
    }

    public function testPreviousFullNameWhenLastNameChanged(): void
    {
        $contract = (new Contracts())->setIsLastNameChanged(true)->setChangedLastName('Петрова Мария Петровна');

        $this->assertSame('Петрова Мария Петровна', ReportMethods::previousFullName(contract: $contract));
    }

    public function testPreviousFullNameWithoutData(): void
    {
        $this->assertSame('–', ReportMethods::previousFullName(contract: new Contracts()));
    }

    public function testShortFullNameGenitive(): void
    {
        $contract = (new Contracts())
            ->setLastNameGenitive('Ивановой')
            ->setFirstName('Мария')
            ->setMiddleName('Петровна');

        $this->assertSame('Ивановой М.П.', ReportMethods::shortFullNameGenitive(contract: $contract));
    }

    public function testShortFullNameGenitiveWithoutGenitiveLastName(): void
    {
        $this->assertSame('', ReportMethods::shortFullNameGenitive(contract: (new Contracts())->setFirstName('Мария')));
    }

    public function testProcedureInitiationDateShortPrefersDecisionDate(): void
    {
        $contract = (new Contracts())
            ->setProcedureInitiationDecisionDate(new \DateTime('2025-05-05'))
            ->setProcedureInitiationResolutionDate(new \DateTime('2025-04-30'));

        $this->assertSame('05.05.2025 г.', ReportMethods::procedureInitiationDateShort(contract: $contract));
    }

    public function testProcedureInitiationDateShortFallsBackToResolutionDate(): void
    {
        $contract = (new Contracts())->setProcedureInitiationResolutionDate(new \DateTime('2025-04-30'));

        $this->assertSame('30.04.2025 г.', ReportMethods::procedureInitiationDateShort(contract: $contract));
    }

    /**
     * @return array<string, array{int, string}>
     */
    public static function childrenVariants(): array
    {
        return [
            'нет детей' => [0, 'несовершеннолетних детей не находилось'],
            'один ребёнок' => [1, 'находился один несовершеннолетний ребёнок – Иванова Анна Ивановна, 20.03.2015 г.р.'],
            'двое детей' => [2, 'находилось двое несовершеннолетних детей – Иванова Анна Ивановна, 20.03.2015 г.р. и Иванов Пётр Иванович, 18.07.2011 г.р.'],
            'трое детей' => [3, 'находилось трое несовершеннолетних детей – Иванова Анна Ивановна, 20.03.2015 г.р., Иванов Пётр Иванович, 18.07.2011 г.р. и Иванов Олег Иванович, 01.09.2019 г.р.'],
        ];
    }

    #[DataProvider('childrenVariants')]
    public function testMinorChildrenDependantsText(int $count, string $expected): void
    {
        $children = [
            ['lastName' => 'Иванова', 'firstName' => 'Анна', 'middleName' => 'Ивановна', 'birthDate' => '2015-03-20'],
            ['lastName' => 'Иванов', 'firstName' => 'Пётр', 'middleName' => 'Иванович', 'birthDate' => '2011-07-18'],
            ['lastName' => 'Иванов', 'firstName' => 'Олег', 'middleName' => 'Иванович', 'birthDate' => '2019-09-01'],
        ];
        $contract = (new Contracts())->setChildren(array_slice($children, 0, $count));

        $this->assertSame($expected, ReportMethods::minorChildrenDependantsText(contract: $contract));
    }

    public function testMoreThanTenChildrenUsesDigits(): void
    {
        $children = array_fill(0, 11, ['lastName' => 'Иванов', 'firstName' => 'Иван', 'middleName' => null, 'birthDate' => '2015-03-20']);
        $contract = (new Contracts())->setChildren($children);

        $this->assertStringStartsWith('находилось 11 несовершеннолетних детей – Иванов Иван, 20.03.2015 г.р., ', ReportMethods::minorChildrenDependantsText(contract: $contract));
    }

    public function testNotifiedCreditorsAreListedOnceInGenitive(): void
    {
        $sberbank = (new Creditor())->setName('Публичное акционерное общество "Сбербанк России"');
        $alfaBank = (new Creditor())->setName('Акционерное общество "Альфа-Банк"');
        $contract = new Contracts();

        foreach ([$sberbank, $alfaBank, $sberbank] as $creditor) {
            $contract->addPreCourtCreditor((new ContractsPreCourtCreditor())->setCreditor($creditor));
        }

        $this->assertSame(
            'публичного акционерного общества «Сбербанк России», акционерного общества «Альфа-Банк»',
            ReportMethods::notifiedCreditorsList(contract: $contract),
        );
    }

    public function testNotifiedCreditorsListIsEmptyWithoutCreditors(): void
    {
        $this->assertSame('', ReportMethods::notifiedCreditorsList(contract: new Contracts()));
    }

    public function testRegistryCreditorsCountCountsDistinctCreditors(): void
    {
        $sberbank = (new Creditor())->setName('ПАО Сбербанк');
        $alfaBank = (new Creditor())->setName('АО Альфа-Банк');
        $contract = new Contracts();

        foreach ([$sberbank, $sberbank, $alfaBank] as $creditor) {
            $contract->addCreditorsClaim((new ContractsCreditorsClaim())->setCreditor($creditor));
        }

        $this->assertSame(2, ReportMethods::registryCreditorsCount(contract: $contract));
    }

    public function testRegistryCreditorsCountIsZeroWithoutClaims(): void
    {
        $this->assertSame(0, ReportMethods::registryCreditorsCount(contract: new Contracts()));
    }
}
