<?php

declare(strict_types=1);

namespace App\Tests\Service\Templates;

use App\Entity\Contracts;
use App\Entity\DocumentTemplate;
use App\Entity\Enum\BankruptcyStage;
use App\Service\Templates\CustomFunction;
use App\Service\Templates\DocumentTemplateProcessor;
use App\Service\Templates\EntityDataResolver;
use PHPUnit\Framework\TestCase;
use Symfony\Component\PropertyAccess\PropertyAccess;

class PropertyInventoryTemplateTest extends TestCase
{
    private const string TEMPLATE_FILE = '/src/document-templates/judicial_report/3. Опись имущества гражданина.docx';
    private const string FULL_NAME_GENITIVE = 'Тереховой Светланы Александровны';

    public function testDebtorDataIsSubstituted(): void
    {
        $documentXml = $this->process(contract: $this->contract());
        $text = $this->text(xml: $documentXml);

        $this->assertStringContainsString('Финансовый управляющий ' . self::FULL_NAME_GENITIVE, $text);
        $this->assertSame(2, substr_count($text, self::FULL_NAME_GENITIVE));
        $this->assertStringContainsString('Терехова Светлана Александровна', $text);
        $this->assertStringContainsString('Смирнова', $text);
        $this->assertStringContainsString('23.10.1982', $text);
        $this->assertStringContainsString('гор. Ленинград', $text);
        $this->assertStringContainsString('782609400697', $text);
        $this->assertStringContainsString('141-362-038 17', $text);
        $this->assertStringContainsString('г. Санкт-Петербург, ул. Савушкина, д. 18, кв. 18', $text);
        $this->assertStringContainsString('серия: 4005 номер: 312038', $text);
    }

    public function testInventoryDateIsSubstituted(): void
    {
        $text = $this->text(xml: $this->process(contract: $this->contract()));

        $this->assertStringContainsString('проведена «02» июля 2025 г.', $text);
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

    public function testPropertyTablesStayFormText(): void
    {
        $text = $this->text(xml: $this->process(contract: $this->contract()));

        foreach ([
            'Земельные участки:',
            'Автомобили легковые:',
            'Денежные средства на расчетных счетах',
            'Наличные денежные средства',
            'Акции и иное участие в коммерческих организациях',
            'Ценные бумаги',
            'Драгоценности, в том числе ювелирные украшения, и другие предметы роскоши:',
            'Дебиторская задолженность',
            'Исключительные права на результаты интеллектуальной деятельности',
        ] as $section) {
            $this->assertStringContainsString($section, $text);
        }
    }

    public function testLastNameWasNotChanged(): void
    {
        $contract = $this->contract()
            ->setIsLastNameChanged(false)
            ->setChangedLastName(null);

        $text = $this->text(xml: $this->process(contract: $contract));

        $this->assertStringNotContainsString('Смирнова', $text);
        $this->assertStringContainsString('—', $text);
    }

    public function testEmptyInventoryDateLeavesNoMarkers(): void
    {
        $contract = $this->contract()->setPropertyInventoryDate(null);

        $text = $this->text(xml: $this->process(contract: $contract));

        $this->assertStringContainsString('проведена', $text);
        $this->assertStringNotContainsString('{{', $text);
        $this->assertStringNotContainsString('2025 г.', $text);
    }

    private function contract(): Contracts
    {
        return (new Contracts())
            ->setLastName('Терехова')
            ->setFirstName('Светлана')
            ->setMiddleName('Александровна')
            ->setLastNameGenitive('Тереховой')
            ->setFirstNameGenitive('Светланы')
            ->setMiddleNameGenitive('Александровны')
            ->setIsLastNameChanged(true)
            ->setChangedLastName('Смирнова')
            ->setBirthDate(new \DateTime('1982-10-23'))
            ->setBirthPlace('гор. Ленинград')
            ->setInn('782609400697')
            ->setSnils('141-362-038 17')
            ->setRegistrationCity('Санкт-Петербург')
            ->setRegistrationStreet('Савушкина')
            ->setRegistrationHouse('18')
            ->setRegistrationApartment('18')
            ->setPassportSeries('4005')
            ->setPassportNumber('312038')
            ->setPropertyInventoryDate(new \DateTime('2025-07-02'));
    }

    private function process(Contracts $contract): string
    {
        $processor = new DocumentTemplateProcessor(
            entityDataResolver: new EntityDataResolver(propertyAccessor: PropertyAccess::createPropertyAccessor()),
            customFunction: new CustomFunction(),
        );

        $template = (new DocumentTemplate())
            ->setName('3. Опись имущества гражданина')
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
