<?php

declare(strict_types=1);

namespace App\Tests\Service\Templates;

use App\Entity\Contracts;
use App\Entity\ContractsCreditorsClaim;
use App\Entity\Creditor;
use App\Entity\DocumentTemplate;
use App\Entity\Enum\BankruptcyStage;
use App\Service\Templates\CustomFunction;
use App\Service\Templates\DocumentTemplateProcessor;
use App\Service\Templates\EntityDataResolver;
use PhpOffice\PhpWord\IOFactory;
use PhpOffice\PhpWord\PhpWord;
use PHPUnit\Framework\TestCase;
use Symfony\Component\PropertyAccess\PropertyAccess;

class TemplateBlocksProcessingTest extends TestCase
{
    private const string TEMPLATES_DIRECTORY = '/src/document-templates';
    private const string CLAIM_RECEIPT_PUBLICATION_SUFFIX = '_2. Публикация о получении требования кредитора.docx';
    private const string MAIN_PART = 'word/document.xml';

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

    public function testClaimReceiptPublicationWithoutClaimsHasNoBlockMarkers(): void
    {
        $document = $this->process(templatePath: $this->claimReceiptPublicationPath(), contract: new Contracts());

        $this->assertStringNotContainsString('${', $this->text(document: $document));
        $this->assertStringNotContainsString('creditors_claims', $document);
    }

    public function testAmpersandInCreditorNameIsWrittenAsText(): void
    {
        $contract = (new Contracts())->addCreditorsClaim(
            (new ContractsCreditorsClaim())->setCreditor((new Creditor())->setName('ООО «М&К Финанс»'))
        );

        $document = $this->process(
            templatePath: $this->createTemplate(lines: ['${creditors_claims}', 'Кредитор: ${$creditorsClaims.creditor.name}', '${/creditors_claims}']),
            contract: $contract,
        );

        $this->assertStringContainsString('Кредитор: ООО «М&К Финанс»', $this->text(document: $document));
    }

    public function testTwoTableBlocksWithSharedVariablesAreBothFilled(): void
    {
        $path = sys_get_temp_dir() . '/' . uniqid('template_blocks_', true) . '.docx';
        $this->paths[] = $path;

        $phpWord = new PhpWord();
        $section = $phpWord->addSection();

        foreach (['registry_main_creditors' => 'creditorName', 'registry_main_claims' => 'amountText'] as $blockName => $property) {
            $table = $section->addTable();
            $table->addRow();
            $table->addCell(1000)->addText('${' . $blockName . '}${$number}');
            $table->addCell(2000)->addText('${$registryMainClaims.entryDate}');
            $table->addCell(4000)->addText('${$registryMainClaims.' . $property . '}${/' . $blockName . '}');
        }

        IOFactory::createWriter($phpWord, 'Word2007')->save($path);

        $document = $this->process(templatePath: $path, contract: RegisterContractFactory::create());
        $text = $this->text(document: $document);

        $this->assertStringNotContainsString('${', $text);
        $this->assertSame(4, preg_match_all('/<w:tr[\s>]/', $document));
        $this->assertMatchesRegularExpression(
            '/1\s*21\.07\.2025\s*' . preg_quote(RegisterContractFactory::SBERBANK, '/') . '.*2\s*29\.07\.2025\s*' . preg_quote(RegisterContractFactory::VTB, '/') . '.*1\s*21\.07\.2025\s*2116862,59.*2\s*29\.07\.2025\s*1500000,00/su',
            $text,
        );
    }

    private function process(string $templatePath, Contracts $contract): string
    {
        $processor = new DocumentTemplateProcessor(
            entityDataResolver: new EntityDataResolver(propertyAccessor: PropertyAccess::createPropertyAccessor()),
            customFunction: new CustomFunction(),
        );

        $template = (new DocumentTemplate())
            ->setName('Шаблон с блоками')
            ->setCategory(BankruptcyStage::JUDICIAL_PROCEDURE)
            ->setPath($templatePath);

        $outputPath = $processor->processTemplate(template: $template, contract: $contract);
        $this->paths[] = $outputPath;

        $archive = new \ZipArchive();
        $archive->open($outputPath);
        $document = (string)$archive->getFromName(self::MAIN_PART);
        $archive->close();

        $dom = new \DOMDocument();
        $this->assertTrue($dom->loadXML($document));

        return $document;
    }

    private function claimReceiptPublicationPath(): string
    {
        $directory = dirname(__DIR__, 3) . self::TEMPLATES_DIRECTORY;

        foreach (scandir($directory) ?: [] as $fileName) {
            if (str_ends_with((string)\Normalizer::normalize($fileName, \Normalizer::FORM_C), self::CLAIM_RECEIPT_PUBLICATION_SUFFIX)) {
                return $directory . '/' . $fileName;
            }
        }

        $this->fail('Шаблон «Публикация о получении требования кредитора» не найден в ' . $directory);
    }

    /**
     * @param array<int, string> $lines
     */
    private function createTemplate(array $lines): string
    {
        $path = sys_get_temp_dir() . '/' . uniqid('template_blocks_', true) . '.docx';
        $this->paths[] = $path;

        $phpWord = new PhpWord();
        $section = $phpWord->addSection();

        foreach ($lines as $line) {
            $section->addText($line);
        }

        IOFactory::createWriter($phpWord, 'Word2007')->save($path);

        return $path;
    }

    private function text(string $document): string
    {
        return html_entity_decode(strip_tags(str_replace('</w:p>', "\n", $document)), ENT_QUOTES | ENT_XML1, 'UTF-8');
    }
}
