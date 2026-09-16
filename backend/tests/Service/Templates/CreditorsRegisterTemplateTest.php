<?php

declare(strict_types=1);

namespace App\Tests\Service\Templates;

use App\Entity\DocumentTemplate;
use App\Entity\Enum\BankruptcyStage;
use App\Service\DateHelperService;
use App\Service\Templates\CustomFunction;
use App\Service\Templates\DocumentTemplateProcessor;
use App\Service\Templates\EntityDataResolver;
use PHPUnit\Framework\TestCase;
use Symfony\Component\PropertyAccess\PropertyAccess;

class CreditorsRegisterTemplateTest extends TestCase
{
    private const string TEMPLATE_FILE = '/src/document-templates/2_creditors_claims_register.docx';
    private const int MAIN_CLAIMS_TABLE = 12;
    private const int SANCTION_CLAIMS_TABLE = 17;
    private const int HEADER_ROWS = 2;

    private string $documentXml;
    private string $footerXml;

    protected function setUp(): void
    {
        $processor = new DocumentTemplateProcessor(
            entityDataResolver: new EntityDataResolver(propertyAccessor: PropertyAccess::createPropertyAccessor()),
            customFunction: new CustomFunction(),
        );

        $template = (new DocumentTemplate())
            ->setName('2. Реестр требований кредиторов')
            ->setCategory(BankruptcyStage::JUDICIAL_REPORT)
            ->setPath(dirname(__DIR__, 3) . self::TEMPLATE_FILE);

        $outputPath = $processor->processTemplate(template: $template, contract: RegisterContractFactory::create());

        $archive = new \ZipArchive();
        $archive->open($outputPath);
        $this->documentXml = (string)$archive->getFromName('word/document.xml');
        $this->footerXml = (string)$archive->getFromName('word/footer1.xml');
        $archive->close();
        unlink($outputPath);
    }

    public function testAllPlaceholdersAndBlocksAreReplaced(): void
    {
        foreach ([$this->text(xml: $this->documentXml), $this->text(xml: $this->footerXml)] as $text) {
            $this->assertStringNotContainsString('{{', $text);
            $this->assertStringNotContainsString('${', $text);
        }

        $this->assertTrue((new \DOMDocument())->loadXML($this->documentXml));
    }

    public function testHeaderAndTotalsAreSubstituted(): void
    {
        $text = $this->text(xml: $this->documentXml);
        $today = new \DateTimeImmutable('today');

        $this->assertStringContainsString(
            'Реестр требований кредиторов Тереховой Светланы Александровны (дата рождения: 23.10.1982, место рождения: гор. Ленинград, СНИЛС: 141-362-038 17, ИНН 782609400697, регистрация по месту жительства: 196233, г. Санкт-Петербург, ул. Савушкина, д. 18, кв. 18) на '
            . $today->format('d') . ' ' . DateHelperService::getMonthNameGenitive(monthNumber: $today->format('n')) . ' ' . $today->format('Y') . ' г.',
            $text,
        );
        $this->assertStringContainsString('Дело № А56-117152/2023', $text);
        $this->assertStringContainsString('Дата открытия: 17.05.2025 г.', $text);
        $this->assertStringContainsString('Размер требований кредиторов третьей очереди' . "\u{00A0}" . '3616862,59 руб.', $text);
        $this->assertStringContainsString('учитываемых отдельно очереди 2206637,98 руб.', $text);
        $this->assertSame(6, substr_count($text, 'Дата закрытия реестра требований кредиторов: 17.07.2025 г.'));
        $this->assertStringContainsString('Всего кредиторов — 2Всего требований кредиторов — 2Суммарный размер требований кредиторов (итог по столбцу № 8 таблицы 12 ) — 3616862,59 руб.Общая сумма погашения требований кредиторов (итог по столбцу № 6 таблицы 13) — 0,00 руб.Процентное отношение погашенной суммы к общей сумме требований кредиторов данной очереди — 0,00%', $text);
        $this->assertStringContainsString('(итог по столбцу № 8 таблицы 18 ) — 2206637,98 руб.', $text);
        $this->assertStringContainsString('Дата ' . $today->format('d.m.Y') . ' г.', $this->text(xml: $this->footerXml));
    }

    public function testClaimRowsAreBuilt(): void
    {
        $mainRows = $this->dataRows(tableIndex: self::MAIN_CLAIMS_TABLE);
        $sanctionRows = $this->dataRows(tableIndex: self::SANCTION_CLAIMS_TABLE);

        $this->assertCount(2, $mainRows);
        $this->assertCount(2, $sanctionRows);
        $this->assertSame(
            '229.07.202522КредитКредитные договоры № 625/0055-123 от 01.02.2020 г., № 633/0055-456 от 03.04.2021 г.01.02.20201500000,00Определение Арбитражного суда города Санкт-Петербурга и Ленинградской области от 25.07.2025 г. по делу № А56-117152/2023—',
            $mainRows[1],
        );
        $this->assertStringContainsString('ШтрафКредитный договор № 93-12345 от 15.03.2019 г.15.03.20191206637,98', $sanctionRows[0]);
        $this->assertStringContainsString('Пени', $sanctionRows[1]);
    }

    public function testOnlyExcludedClaimsCountsStayRed(): void
    {
        preg_match_all('/<w:r\b[^>]*>(?:(?!<\/w:r>).)*<w:color w:val="(?:FF0000|00B050)"\/>(?:(?!<\/w:r>).)*<\/w:r>/su', $this->documentXml, $coloredRuns);

        $this->assertSame(['0', '0'], array_values(array_filter(
            array_map(fn (string $run): string => trim($this->text(xml: $run)), $coloredRuns[0]),
            static fn (string $text): bool => $text !== '',
        )));
    }

    /**
     * @return array<int, string>
     */
    private function dataRows(int $tableIndex): array
    {
        preg_match_all('/<w:tbl>.*?<\/w:tbl>/s', $this->documentXml, $tables);
        preg_match_all('/<w:tr[\s>].*?<\/w:tr>/s', $tables[0][$tableIndex], $rows);

        return array_map(
            fn (string $row): string => $this->text(xml: $row),
            array_slice($rows[0], self::HEADER_ROWS),
        );
    }

    private function text(string $xml): string
    {
        return html_entity_decode(strip_tags($xml), ENT_QUOTES | ENT_XML1, 'UTF-8');
    }
}
