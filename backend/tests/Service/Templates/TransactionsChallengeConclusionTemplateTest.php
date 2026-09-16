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

class TransactionsChallengeConclusionTemplateTest extends TestCase
{
    private const string TEMPLATE_FILE = '/src/document-templates/judicial_report/4. Заключение о наличии или об отсутствии оснований для оспаривания сделок должника.docx';
    private const string COURT_NAME = 'Арбитражный суд города Санкт-Петербурга и Ленинградской области';

    public function testCaseAndDebtorDataAreSubstituted(): void
    {
        $text = $this->text(xml: $this->process(contract: $this->contract()));

        $this->assertStringContainsString('Ф.И.О. должника: Терехова Светлана Александровна', $text);
        $this->assertSame(1, substr_count($text, self::COURT_NAME));
        $this->assertStringNotContainsString('Арбитражный суд ' . self::COURT_NAME, $text);
        $this->assertStringContainsString('А56-117152/2023', $text);
        $this->assertStringContainsString('23.10.1982', $text);
        $this->assertStringContainsString('гор. Ленинград', $text);
        $this->assertStringContainsString('782609400697', $text);
        $this->assertStringContainsString('141-362-038 17', $text);
        $this->assertStringContainsString('г. Санкт-Петербург, ул. Савушкина, д. 18, кв. 18', $text);
        $this->assertStringContainsString(
            'оснований для оспаривания сделок Тереховой Светланы Александровны',
            $text,
        );
        $this->assertSame(2, substr_count($text, 'Тереховой Светланы Александровны'));
    }

    public function testCaseDatesAreSubstituted(): void
    {
        $text = $this->text(xml: $this->process(contract: $this->contract()));

        $this->assertStringContainsString('«01» апреля 2025 г.', $text);
        $this->assertStringContainsString('«08» апреля 2025 г.', $text);
        $this->assertSame(3, substr_count($text, '«05» мая 2025 г.'));
    }

    public function testAnalysisPeriodIsThreeYearsBeforeSubmission(): void
    {
        $text = $this->text(xml: $this->process(contract: $this->contract()));

        $this->assertStringContainsString(
            'заключенных должником в период с «01» апреля 2022 г. по «05» мая 2025 г.',
            $text,
        );
    }

    public function testInsuranceContractComesFromFinancialManager(): void
    {
        $text = $this->text(xml: $this->process(contract: $this->contract()));

        $this->assertStringContainsString(
            'Договор № Arbitr-3980975400-26366 от 08.09.2022 г., срок действия с 09.09.2022 г. по 08.09.2026 г.',
            $text,
        );
    }

    public function testAllPlaceholdersAreReplacedAndColorsRemoved(): void
    {
        $documentXml = $this->process(contract: $this->contract());
        $text = $this->text(xml: $documentXml);

        $this->assertStringNotContainsString('{{', $text);
        $this->assertStringNotContainsString('${', $text);
        $this->assertTrue((new \DOMDocument())->loadXML($documentXml));
        $this->assertSame(0, preg_match_all('/<w:color w:val="(?:00B050|FF0000)"\/>/', $documentXml));
    }

    public function testEmptyContractLeavesNoMarkers(): void
    {
        $documentXml = $this->process(contract: new Contracts());
        $text = $this->text(xml: $documentXml);

        $this->assertStringNotContainsString('{{', $text);
        $this->assertStringNotContainsString('${', $text);
        $this->assertStringContainsString('Сведения о должнике', $text);
        $this->assertStringContainsString('заключенных должником в период с  по ', $text);
    }

    private function contract(): Contracts
    {
        $financialManager = (new FinancialManager())
            ->setFio('Федорец Василий Владимирович')
            ->setInsuranceContractNumber('Arbitr-3980975400-26366')
            ->setInsuranceContractDate(new \DateTime('2022-09-08'))
            ->setInsuranceStartDate(new \DateTime('2022-09-09'))
            ->setInsuranceEndDate(new \DateTime('2026-09-08'));

        return (new Contracts())
            ->setLastName('Терехова')
            ->setFirstName('Светлана')
            ->setMiddleName('Александровна')
            ->setLastNameGenitive('Тереховой')
            ->setFirstNameGenitive('Светланы')
            ->setMiddleNameGenitive('Александровны')
            ->setBirthDate(new \DateTime('1982-10-23'))
            ->setBirthPlace('гор. Ленинград')
            ->setInn('782609400697')
            ->setSnils('141-362-038 17')
            ->setRegistrationCity('Санкт-Петербург')
            ->setRegistrationStreet('Савушкина')
            ->setRegistrationHouse('18')
            ->setRegistrationApartment('18')
            ->setCaseNumber('А56-117152/2023')
            ->setCourt((new Court())->setName(self::COURT_NAME))
            ->setFinancialManager($financialManager)
            ->setCourtApplicationSubmissionDate(new \DateTime('2025-04-01'))
            ->setCaseInitiationDate(new \DateTime('2025-04-08'))
            ->setProcedureInitiationDecisionDate(new \DateTime('2025-05-05'));
    }

    private function process(Contracts $contract): string
    {
        $processor = new DocumentTemplateProcessor(
            entityDataResolver: new EntityDataResolver(propertyAccessor: PropertyAccess::createPropertyAccessor()),
            customFunction: new CustomFunction(),
        );

        $template = (new DocumentTemplate())
            ->setName('4. Заключение о наличии или об отсутствии оснований для оспаривания сделок должника')
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
