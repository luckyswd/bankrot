<?php

declare(strict_types=1);

namespace App\Tests\Service\Templates;

use App\Entity\Contracts;
use App\Entity\Court;
use App\Entity\DocumentTemplate;
use App\Entity\Enum\BankruptcyStage;
use App\Entity\FinancialManager;
use App\Service\Templates\CustomFunction;
use App\Service\Templates\DocumentTemplateProcessor;
use App\Service\Templates\EntityDataResolver;
use PHPUnit\Framework\TestCase;
use Symfony\Component\PropertyAccess\PropertyAccess;

class FinancialAnalysisTemplateTest extends TestCase
{
    private const string TEMPLATE_FILE = '/src/document-templates/judicial_report/6. Анализ финансового состояния гражданина.docx';
    private const string COURT_NAME = 'Арбитражный суд города Санкт-Петербурга и Ленинградской области';
    private const string COURT_TAIL = 'города Санкт-Петербурга и Ленинградской области';
    private const string COURT_ADDRESS = '191124, г. Санкт-Петербург, ул. Смольного, д. 6';

    public function testHeaderAndCourtDataAreSubstituted(): void
    {
        $text = $this->text(xml: $this->process(contract: $this->contract()));

        $this->assertStringContainsString('Финансовый управляющий Барелюк Татьяны Александровны', $text);
        $this->assertStringContainsString('в Арбитражный суд ' . self::COURT_TAIL, $text);
        $this->assertStringContainsString(self::COURT_ADDRESS, $text);
        $this->assertStringContainsString('Дело № А56-123932/2023', $text);
        $this->assertStringContainsString(self::COURT_NAME, $text);
    }

    public function testDebtorDetailsAreSubstituted(): void
    {
        $text = $this->text(xml: $this->process(contract: $this->contract()));

        $this->assertStringContainsString(
            'Барелюк Татьяна Александровна, дата рождения: 15.04.1960 г., место рождения:',
            $text,
        );
        $this->assertStringContainsString(
            'с. Левокумское, СНИЛС 013-041-516 76, ИНН 261300355440, адрес: г. Санкт-Петербург, ул. Пушкина, д. 98, кв. 6',
            $text,
        );
        $this->assertStringContainsString('Финансовый анализ Барелюк Т.А. (далее по тексту – Должник)', $text);
    }

    public function testJudicialActsAndAnalysisDates(): void
    {
        $text = $this->text(xml: $this->process(contract: $this->contract()));

        $this->assertSame(
            2,
            substr_count($text, 'Решение Арбитражного суда ' . self::COURT_TAIL . ' от «23» сентября 2025 г. по делу № А56-123932/2023'),
        );
        $this->assertStringContainsString('«14» мая 2026 г.', $text);
        $this->assertStringContainsString('Дополнен «09» августа 2026 г.', $text);
        $this->assertStringContainsString(
            'Договор № Arbitr-3980975400-19805 от 30.07.2026 г., срок действия с 09.09.2026 г. по 08.09.2027 г.',
            $text,
        );
    }

    public function testSocialPaymentsAndSubsistenceMinimum(): void
    {
        $text = $this->text(xml: $this->process(contract: $this->contract()));

        $this->assertStringContainsString('социальных выплат должник является.', $text);
        $this->assertStringContainsString('для населения Санкт-Петербурга установлен Постановлением Правительства Санкт-Петербурга', $text);
        $this->assertStringContainsString('величина прожиточного минимума на 2026 год', $text);
    }

    public function testAllPlaceholdersAreReplacedAndGreenRemoved(): void
    {
        $documentXml = $this->process(contract: $this->contract());
        $text = $this->text(xml: $documentXml);

        $this->assertStringNotContainsString('{{', $text);
        $this->assertStringNotContainsString('${', $text);
        $this->assertTrue((new \DOMDocument())->loadXML($documentXml));
        $this->assertSame(0, preg_match_all('/<w:color w:val="00B050"\/>/', $documentXml));
        $this->assertGreaterThan(0, preg_match_all('/<w:color w:val="FF0000"\/>/', $documentXml));
    }

    public function testLeningradRegionAndNoSupplement(): void
    {
        $contract = $this->contract()
            ->setFinancialAnalysisSupplementDate(null)
            ->setIsSocialPaymentsRecipient(false)
            ->setRegistrationRegion('Ленинградская обл.')
            ->setRegistrationCity('Всеволожск');

        $text = $this->text(xml: $this->process(contract: $contract));

        $this->assertStringNotContainsString('Дополнен', $text);
        $this->assertStringContainsString('социальных выплат должник не является.', $text);
        $this->assertStringContainsString('для населения Ленинградской области установлен Постановлением Правительства Ленинградской области', $text);
    }

    public function testEmptyContractLeavesNoMarkers(): void
    {
        $text = $this->text(xml: $this->process(contract: new Contracts()));

        $this->assertStringNotContainsString('{{', $text);
        $this->assertStringNotContainsString('${', $text);
        $this->assertStringContainsString('АНАЛИЗ ФИНАНСОВОГО СОСТОЯНИЯ ГРАЖДАНИНА', $text);
    }

    private function contract(): Contracts
    {
        $financialManager = (new FinancialManager())
            ->setFio('Федорец Василий Владимирович')
            ->setInsuranceContractNumber('Arbitr-3980975400-19805')
            ->setInsuranceContractDate(new \DateTime('2026-07-30'))
            ->setInsuranceStartDate(new \DateTime('2026-09-09'))
            ->setInsuranceEndDate(new \DateTime('2027-09-08'));

        $court = (new Court())
            ->setName(self::COURT_NAME)
            ->setAddress(self::COURT_ADDRESS);

        return (new Contracts())
            ->setLastName('Барелюк')
            ->setFirstName('Татьяна')
            ->setMiddleName('Александровна')
            ->setLastNameGenitive('Барелюк')
            ->setFirstNameGenitive('Татьяны')
            ->setMiddleNameGenitive('Александровны')
            ->setBirthDate(new \DateTime('1960-04-15'))
            ->setBirthPlace('с. Левокумское')
            ->setInn('261300355440')
            ->setSnils('013-041-516 76')
            ->setRegistrationCity('Санкт-Петербург')
            ->setRegistrationStreet('Пушкина')
            ->setRegistrationHouse('98')
            ->setRegistrationApartment('6')
            ->setCaseNumber('А56-123932/2023')
            ->setCourt($court)
            ->setFinancialManager($financialManager)
            ->setProcedureInitiationDecisionDate(new \DateTime('2025-09-23'))
            ->setBankruptcySignsEfrsbPublicationDate(new \DateTime('2026-05-14'))
            ->setFinancialAnalysisSupplementDate(new \DateTime('2026-08-09'))
            ->setIsSocialPaymentsRecipient(true);
    }

    private function process(Contracts $contract): string
    {
        $processor = new DocumentTemplateProcessor(
            entityDataResolver: new EntityDataResolver(propertyAccessor: PropertyAccess::createPropertyAccessor()),
            customFunction: new CustomFunction(),
        );

        $template = (new DocumentTemplate())
            ->setName('6. Анализ финансового состояния гражданина')
            ->setCategory(BankruptcyStage::JUDICIAL_REPORT)
            ->setPath(dirname(__DIR__, 3) . self::TEMPLATE_FILE);

        $outputPath = $processor->processTemplate(template: $template, contract: $contract);

        $archive = new \ZipArchive();
        $archive->open($outputPath);
        $documentXml = (string)$archive->getFromName('word/document.xml');
        $archive->close();
        unlink($outputPath);

        return $documentXml;
    }

    private function text(string $xml): string
    {
        return html_entity_decode(strip_tags($xml), ENT_QUOTES | ENT_XML1, 'UTF-8');
    }
}
