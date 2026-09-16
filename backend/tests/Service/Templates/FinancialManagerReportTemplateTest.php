<?php

declare(strict_types=1);

namespace App\Tests\Service\Templates;

use App\Entity\Contracts;
use App\Entity\ContractsProperty;
use App\Entity\DocumentTemplate;
use App\Entity\Enum\PropertySubtype;
use App\Entity\Enum\BankruptcyStage;
use App\Service\Templates\CustomFunction;
use App\Service\Templates\DocumentTemplateProcessor;
use App\Service\Templates\EntityDataResolver;
use PHPUnit\Framework\TestCase;
use Symfony\Component\PropertyAccess\PropertyAccess;

class FinancialManagerReportTemplateTest extends TestCase
{
    private const string TEMPLATE_FILE = '/src/document-templates/judicial_report/1. Отчёт финансового управляющего о результатах реализации имущества.docx';
    private const string NBSP = "\u{00A0}";

    private string $documentXml;

    protected function setUp(): void
    {
        $processor = new DocumentTemplateProcessor(
            entityDataResolver: new EntityDataResolver(propertyAccessor: PropertyAccess::createPropertyAccessor()),
            customFunction: new CustomFunction(),
        );

        $template = (new DocumentTemplate())
            ->setName('1. Отчёт финансового управляющего о результатах реализации имущества')
            ->setCategory(BankruptcyStage::JUDICIAL_REPORT)
            ->setPath(dirname(__DIR__, 3) . self::TEMPLATE_FILE);

        $this->documentXml = $this->process(contract: ReportContractFactory::create());
    }

    private function process(Contracts $contract): string
    {
        $processor = new DocumentTemplateProcessor(
            entityDataResolver: new EntityDataResolver(propertyAccessor: PropertyAccess::createPropertyAccessor()),
            customFunction: new CustomFunction(),
        );

        $template = (new DocumentTemplate())
            ->setName('1. Отчёт финансового управляющего о результатах реализации имущества')
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

    public function testPropertyTableIsFilledFromCase(): void
    {
        $contract = ReportContractFactory::create()
            ->addProperty(
                (new ContractsProperty())
                    ->setSubtype(PropertySubtype::APARTMENT)
                    ->setName('квартира в многоквартирном доме, кадастровый номер 78:15:0000:15:350')
                    ->setManagerValuation('1 000 000,00')
                    ->setAppraiserValuation('1 200 000,00')
                    ->setIsExcludedFromEstate(true)
                    ->setExcludedValuation('1 000 000,00')
                    ->setExclusionReason('единственное пригодное для проживания жильё')
            )
            ->addProperty(
                (new ContractsProperty())
                    ->setSubtype(PropertySubtype::CAR)
                    ->setName('LADA GRANTA, 2019 г. в.')
                    ->setManagerValuation('253 453,82')
            );

        $text = html_entity_decode(strip_tags($this->process(contract: $contract)), ENT_QUOTES | ENT_XML1, 'UTF-8');

        $this->assertStringContainsString('1. Недвижимое имущество всего, в том числе:1000000,001200000,00–1000000,00', $text);
        $this->assertStringContainsString('квартира в многоквартирном доме, кадастровый номер 78:15:0000:15:3501000000,00', $text);
        $this->assertStringContainsString('1000000,00, единственное пригодное для проживания жильё', $text);
        $this->assertStringContainsString('2. Движимое имущество всего, в том числе:253453,820,00–0,00', $text);
        $this->assertStringContainsString('LADA GRANTA, 2019 г. в.253453,82', $text);
        $this->assertStringContainsString('Всего имущества1253453,821200000,00–1000000,00', $text);
    }

    public function testPropertyTableKeepsFormWhenCaseHasNoProperty(): void
    {
        $text = $this->documentText();

        $this->assertStringContainsString('1. Недвижимое имущество всего, в том числе:0,000,00–0,00', $text);
        $this->assertStringContainsString('2. Движимое имущество всего, в том числе:0,000,00–0,00', $text);
        $this->assertStringContainsString('Всего имущества0,000,00–0,00', $text);
    }

    public function testAllPlaceholdersAreReplaced(): void
    {
        $this->assertStringNotContainsString('{{', $this->documentText());
        $this->assertStringNotContainsString('}}', $this->documentText());
    }

    public function testCaseDataIsSubstituted(): void
    {
        $text = $this->documentText();

        $this->assertStringContainsString('Иванова Мария Петровна', $text);
        $this->assertStringContainsString('Договор № Arbitr-3980975400-26366 от 08.09.2022 г., срок действия с 09.09.2022 г. по 08.09.2026 г.', $text);
        $this->assertStringContainsString('за должником и его бывшим супругом автотранспортных средствах', $text);
        $this->assertStringContainsString('Дата закрытия реестра кредиторов«07» апреля 2025 г.', $text);
        $this->assertStringContainsString('Финансовый анализ проведен «14» мая 2026 г. Результаты', $text);
        $this->assertStringContainsString('560,55–29,67530,88', $text);
        $this->assertStringContainsString('7' . self::NBSP . '866,05', $text);
        $this->assertStringContainsString('у должника находилось двое несовершеннолетних детей – Иванова Анна Ивановна, 20.03.2015 г.р. и Иванов Пётр Иванович, 18.07.2011 г.р., при этом', $text);
    }

    public function testManualFragmentsStayRed(): void
    {
        foreach ([
            '* указана кадастровая стоимость объекта недвижимости',
            '30.12.2025 г.',
            'Постановлением Правительства Санкт-Петербурга № 682',
        ] as $fragment) {
            $this->assertMatchesRegularExpression(
                '/<w:color w:val="FF0000"\/>(?:(?!<\/w:r>).)*' . preg_quote($fragment, '/') . '/su',
                $this->documentXml,
                sprintf('Фрагмент «%s» должен остаться красным', $fragment),
            );
        }
    }

    private function documentText(): string
    {
        return html_entity_decode(strip_tags($this->documentXml), ENT_QUOTES | ENT_XML1, 'UTF-8');
    }
}
