<?php

declare(strict_types=1);

namespace App\Tests\Service\Templates;

use App\Service\Templates\OptimizedTemplateProcessor;
use PhpOffice\PhpWord\IOFactory;
use PhpOffice\PhpWord\PhpWord;
use PHPUnit\Framework\TestCase;

class OptimizedTemplateProcessorTest extends TestCase
{
    private const string MAIN_PART = 'word/document.xml';
    private const string TABLE_ROW_PATTERN = '/<w:tr[\s>]/';

    /**
     * @var array<int, string>
     */
    private array $paths = [];

    protected function tearDown(): void
    {
        foreach ($this->paths as $path) {
            if (file_exists($path)) {
                unlink($path);
            }
        }
    }

    public function testTableRowIsClonedForEachItem(): void
    {
        $processor = $this->createProcessor();

        $processor->cloneBlock(
            blockName: 'rows',
            clones: 2,
            variableReplacements: [
                ['$number' => '1', '$item.name' => 'Сбербанк'],
                ['$number' => '2', '$item.name' => 'ВТБ'],
            ],
        );

        $document = $this->save(processor: $processor);

        $this->assertSame(4, preg_match_all(self::TABLE_ROW_PATTERN, $document));
        $this->assertStringNotContainsString('rows}', $document);
        $text = $this->text(document: $document);
        $this->assertMatchesRegularExpression('/Наименование.*1.*Сбербанк.*2.*ВТБ.*Итого/su', $text);

        preg_match_all('/<w:tr[\s>].*?<\/w:tr>/s', $document, $rows);
        $this->assertSame(
            $this->cellProperties(row: $rows[0][1]),
            $this->cellProperties(row: $rows[0][2]),
        );
    }

    public function testZeroClonesRemovesTableRow(): void
    {
        $processor = $this->createProcessor();

        $processor->cloneBlock(blockName: 'rows', clones: 0, variableReplacements: []);

        $document = $this->save(processor: $processor);

        $this->assertSame(2, preg_match_all(self::TABLE_ROW_PATTERN, $document));
        $this->assertSame(['№Наименование', 'Итого'], $this->tableTexts(document: $document));
        $this->assertStringNotContainsString('rows}', $document);
    }

    public function testTextBlockIsClonedWithoutTouchingTable(): void
    {
        $processor = $this->createProcessor();

        $processor->cloneBlock(
            blockName: 'list',
            clones: 2,
            variableReplacements: [['$item.name' => 'Первый'], ['$item.name' => 'Второй']],
        );

        $document = $this->save(processor: $processor);
        $text = $this->text(document: $document);

        $this->assertStringContainsString('Элемент Первый', $text);
        $this->assertStringContainsString('Элемент Второй', $text);
        $this->assertStringNotContainsString('list}', $text);
        $this->assertSame(3, preg_match_all(self::TABLE_ROW_PATTERN, $document));
    }

    private function createProcessor(): OptimizedTemplateProcessor
    {
        $phpWord = new PhpWord();
        $section = $phpWord->addSection();

        $table = $section->addTable(['borderSize' => 6]);
        $table->addRow();
        $table->addCell(1000)->addText('№');
        $table->addCell(4000)->addText('Наименование');
        $table->addRow();
        $table->addCell(1000)->addText('${rows}${$number}');
        $table->addCell(4000)->addText('${$item.name}${/rows}');
        $table->addRow();
        $table->addCell(1000)->addText('');
        $table->addCell(4000)->addText('Итого');

        $section->addText('${list}');
        $section->addText('Элемент ${$item.name}');
        $section->addText('${/list}');

        $path = $this->temporaryPath();
        IOFactory::createWriter($phpWord, 'Word2007')->save($path);

        $processor = new OptimizedTemplateProcessor($path);
        $processor->setMacroChars('${', '}');

        return $processor;
    }

    private function save(OptimizedTemplateProcessor $processor): string
    {
        $path = $this->temporaryPath();
        $processor->saveAs($path);

        $zip = new \ZipArchive();
        $zip->open($path);
        $document = (string)$zip->getFromName(self::MAIN_PART);
        $zip->close();

        $dom = new \DOMDocument();
        $this->assertTrue($dom->loadXML($document));

        return $document;
    }

    private function text(string $document): string
    {
        return html_entity_decode(strip_tags(str_replace('</w:p>', "\n", $document)));
    }

    /**
     * @return array<int, string>
     */
    private function tableTexts(string $document): array
    {
        preg_match_all('/<w:tr[\s>].*?<\/w:tr>/s', $document, $rows);

        return array_map(static fn (string $row): string => trim(strip_tags($row)), $rows[0]);
    }

    /**
     * @return array<int, string>
     */
    private function cellProperties(string $row): array
    {
        preg_match_all('/<w:tcPr>.*?<\/w:tcPr>/s', $row, $properties);

        return $properties[0];
    }

    private function temporaryPath(): string
    {
        $path = sys_get_temp_dir() . '/' . uniqid('template_blocks_', true) . '.docx';
        $this->paths[] = $path;

        return $path;
    }
}
