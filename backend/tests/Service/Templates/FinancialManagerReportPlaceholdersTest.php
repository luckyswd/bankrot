<?php

declare(strict_types=1);

namespace App\Tests\Service\Templates;

use App\Service\Templates\EntityDataResolver;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use Symfony\Component\PropertyAccess\PropertyAccess;

class FinancialManagerReportPlaceholdersTest extends TestCase
{
    private const string NBSP = "\u{00A0}";

    /**
     * @return array<string, array{string, string}>
     */
    public static function placeholders(): array
    {
        return [
            'ФИО должника' => ['fullName', 'Иванова Мария Петровна'],
            'ФИО в родительном падеже' => ['fullNameGenitive', 'Ивановой Марии Петровны'],
            'ранее присвоенные ФИО' => ['previousFullName', 'Петрова Мария Петровна'],
            'номер дела' => ['caseNumber', 'А56-12578/2025'],
            'суд' => ['court.name', 'Арбитражный суд города Санкт-Петербурга и Ленинградской области'],
            'дата судебного акта' => ['procedureInitiationDateForPublication', '«05» мая 2025 г.'],
            'продление процедуры' => ['procedureExtensionText', '12.03.2026 г., 10.09.2026 г.'],
            'договор страхования' => ['financialManager.insuranceContractDescription', 'Arbitr-3980975400-26366 от 08.09.2022 г., срок действия с 09.09.2022 г. по 08.09.2026 г.'],
            'дата описи' => ['propertyInventoryDateText', '«02» июля 2025 г.'],
            'бывший супруг' => ['spouseInstrumental', ' и его бывшим супругом'],
            'отдел ЗАГС' => ['zagsDepartmentText', 'отдел ЗАГС Комитета по делам ЗАГС Правительства Санкт-Петербурга'],
            'период справки ЗАГС' => ['zagsCertificatePeriodText', '«11» февраля 2009 г. по «10» июля 2025 г.'],
            'номер издания Коммерсантъ' => ['procedureInitiationKommersantIssueNumber', '139 (7101)'],
            'номер объявления' => ['procedureInitiationKommersantAdNumber', '78230149776'],
            'дата публикации в Коммерсантъ' => ['procedureInitiationKommersantPublicationDateText', '«07» февраля 2025 г.'],
            'номер сообщения ЕФРСБ' => ['procedureInitiationEfrsbMessageNumber', '3134699'],
            'дата сообщения ЕФРСБ' => ['procedureInitiationEfrsbMessageDateText', '«18» февраля 2025 г.'],
            'дата уведомления кредиторов' => ['procedureInitiationCreditorsNotificationDateText', '«20» февраля 2025 г.'],
            'адресаты уведомлений' => ['notifiedCreditorsList', 'публичного акционерного общества «Сбербанк России», акционерного общества «Альфа-Банк»'],
            'дата закрытия реестра' => ['registryClosingDateText', '«07» апреля 2025 г.'],
            'всего рассмотрено' => ['claimsConsideredCount', '2'],
            'включено в реестр' => ['claimsIncludedCount', '2'],
            'отказано во включении' => ['claimsRejectedCount', '0'],
            'кредиторов в реестре' => ['registryCreditorsCount', '2'],
            'ЕФРСБ, размер' => ['efrsbExpensesAmountText', '560,55'],
            'ЕФРСБ, погашено' => ['efrsbExpensesPaidText', '29,67'],
            'ЕФРСБ, остаток' => ['efrsbExpensesUnpaidText', '530,88'],
            'почта, размер' => ['postalExpensesAmountText', '1' . self::NBSP . '490,00'],
            'почта, погашено' => ['postalExpensesPaidText', '0,00'],
            'почта, остаток' => ['postalExpensesUnpaidText', '1' . self::NBSP . '490,00'],
            'газеты, размер' => ['newspaperExpensesAmountText', '7' . self::NBSP . '866,05'],
            'газеты, погашено' => ['newspaperExpensesPaidText', '0,00'],
            'газеты, остаток' => ['newspaperExpensesUnpaidText', '7' . self::NBSP . '866,05'],
            'будущие ЕФРСБ, размер' => ['futureEfrsbExpensesAmountText', '1' . self::NBSP . '121,10'],
            'будущие ЕФРСБ, погашено' => ['futureEfrsbExpensesPaidText', '0,00'],
            'будущие ЕФРСБ, остаток' => ['futureEfrsbExpensesUnpaidText', '1' . self::NBSP . '121,10'],
            'дата финансового анализа' => ['bankruptcySignsEfrsbPublicationDateText', '«14» мая 2026 г.'],
            'период работы с' => ['procedureInitiationDateShort', '05.05.2025 г.'],
            'собрание кредиторов' => ['shortFullNameGenitive', 'Ивановой М.П.'],
            'дети' => ['minorChildrenDependantsText', 'находилось двое несовершеннолетних детей – Иванова Анна Ивановна, 20.03.2015 г.р. и Иванов Пётр Иванович, 18.07.2011 г.р.'],
        ];
    }

    #[DataProvider('placeholders')]
    public function testPlaceholderResolvesToDocumentText(string $path, string $expected): void
    {
        $resolver = new EntityDataResolver(propertyAccessor: PropertyAccess::createPropertyAccessor());

        $this->assertSame($expected, $resolver->resolveValue(contract: ReportContractFactory::create(), path: $path));
    }
}
