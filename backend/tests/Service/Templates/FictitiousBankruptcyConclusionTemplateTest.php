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

class FictitiousBankruptcyConclusionTemplateTest extends TestCase
{
    private const string TEMPLATE_FILE = '/src/document-templates/judicial_report/5. Заключение о наличии (отсутствии) признаков фиктивного или преднамеренного банкротства.docx';
    private const string COURT_NAME = 'Арбитражный суд города Санкт-Петербурга и Ленинградской области';
    private const string COURT_TAIL = 'города Санкт-Петербурга и Ленинградской области';
    private const string FULL_NAME_GENITIVE = 'Плехановой Анны Алексеевны';

    public function testDebtorAndCaseDataAreSubstituted(): void
    {
        $text = $this->text(xml: $this->process(contract: $this->contract()));

        $this->assertStringContainsString('Плеханова Анна Алексеевна', $text);
        $this->assertStringContainsString('16.06.1975', $text);
        $this->assertStringContainsString('г. Мурманск', $text);
        $this->assertStringContainsString('519300468029', $text);
        $this->assertStringContainsString('129-305-890 67', $text);
        $this->assertStringContainsString('Ленинградская обл., г. Всеволожск, ул. Мира, д. 23', $text);
        $this->assertSame(2, substr_count($text, 'А56-36284/2025'));
        $this->assertSame(5, substr_count($text, self::FULL_NAME_GENITIVE));
    }

    public function testCourtIsSubstitutedInBothForms(): void
    {
        $text = $this->text(xml: $this->process(contract: $this->contract()));

        $this->assertStringContainsString('дело о банкротстве' . self::COURT_NAME, $text);
        $this->assertStringContainsString('Определения Арбитражного суда ' . self::COURT_TAIL . ' от 22.05.2025 г.', $text);
        $this->assertStringNotContainsString('Арбитражный суд ' . self::COURT_NAME, $text);
    }

    public function testJudicialActDatesAreSubstituted(): void
    {
        $text = $this->text(xml: $this->process(contract: $this->contract()));

        $this->assertSame(2, substr_count($text, '«02» июля 2025 г.'));
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
        $text = $this->text(xml: $this->process(contract: new Contracts()));

        $this->assertStringNotContainsString('{{', $text);
        $this->assertStringNotContainsString('${', $text);
        $this->assertStringContainsString('Выявление признаков преднамеренного банкротства', $text);
        $this->assertStringContainsString('следует сделать вывод о том, что признаки фиктивного банкротства отсутствуют', $text);
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
            ->setLastName('Плеханова')
            ->setFirstName('Анна')
            ->setMiddleName('Алексеевна')
            ->setLastNameGenitive('Плехановой')
            ->setFirstNameGenitive('Анны')
            ->setMiddleNameGenitive('Алексеевны')
            ->setBirthDate(new \DateTime('1975-06-16'))
            ->setBirthPlace('г. Мурманск')
            ->setInn('519300468029')
            ->setSnils('129-305-890 67')
            ->setRegistrationRegion('Ленинградская обл.')
            ->setRegistrationCity('Всеволожск')
            ->setRegistrationStreet('Мира')
            ->setRegistrationHouse('23')
            ->setCaseNumber('А56-36284/2025')
            ->setCourt((new Court())->setName(self::COURT_NAME))
            ->setFinancialManager($financialManager)
            ->setCaseInitiationDate(new \DateTime('2025-05-22'))
            ->setProcedureInitiationDecisionDate(new \DateTime('2025-07-02'));
    }

    private function process(Contracts $contract): string
    {
        $processor = new DocumentTemplateProcessor(
            entityDataResolver: new EntityDataResolver(propertyAccessor: PropertyAccess::createPropertyAccessor()),
            customFunction: new CustomFunction(),
        );

        $template = (new DocumentTemplate())
            ->setName('5. Заключение о наличии (отсутствии) признаков фиктивного или преднамеренного банкротства')
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
