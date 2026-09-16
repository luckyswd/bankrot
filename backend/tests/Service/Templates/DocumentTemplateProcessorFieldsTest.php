<?php

declare(strict_types=1);

namespace App\Tests\Service\Templates;

use App\Entity\Contracts;
use App\Entity\DocumentTemplate;
use App\Entity\Enum\BankruptcyStage;
use App\Service\Templates\CustomFunction;
use App\Service\Templates\DocumentTemplateProcessor;
use App\Service\Templates\EntityDataResolver;
use PhpOffice\PhpWord\IOFactory;
use PhpOffice\PhpWord\PhpWord;
use PHPUnit\Framework\TestCase;
use Symfony\Component\PropertyAccess\PropertyAccess;

class DocumentTemplateProcessorFieldsTest extends TestCase
{
    /**
     * @var array<int, string>
     */
    private array $templatePaths = [];

    protected function tearDown(): void
    {
        foreach ($this->templatePaths as $templatePath) {
            if (file_exists($templatePath)) {
                unlink($templatePath);
            }
        }
    }

    public function testFieldsInsideReportMethodsAreDetected(): void
    {
        $fields = $this->extractFields(lines: [
            'Дата закрытия реестра кредиторов {{ registryClosingDateText }}',
            'за должником{{ spouseInstrumental }} автотранспортных средствах',
        ]);

        $this->assertContains('judicial_procedure_initiation.procedureInitiationKommersantPublicationDate', $fields);
        $this->assertContains('basic_info.maritalStatus', $fields);
        $this->assertContains('basic_info.gender', $fields);
    }

    public function testFieldsInsideRegisterMethodsAreDetected(): void
    {
        $fields = $this->extractFields(lines: [
            'Размер требований кредиторов третьей очереди {{ registryMainAmountText }} руб.',
            'Дата открытия: {{ registryOpeningDateShort }}',
        ]);

        $this->assertContains('judicial_procedure.creditorsClaims', $fields);
        $this->assertContains('judicial_procedure_initiation.procedureInitiationKommersantPublicationDate', $fields);
    }

    /**
     * @param array<int, string> $lines
     *
     * @return array<int, string>
     */
    private function extractFields(array $lines): array
    {
        $templatePath = sys_get_temp_dir() . '/' . uniqid('template_fields_', true) . '.docx';
        $this->templatePaths[] = $templatePath;

        $phpWord = new PhpWord();
        $section = $phpWord->addSection();

        foreach ($lines as $line) {
            $section->addText($line);
        }

        IOFactory::createWriter($phpWord, 'Word2007')->save($templatePath);

        $processor = new DocumentTemplateProcessor(
            entityDataResolver: new EntityDataResolver(propertyAccessor: PropertyAccess::createPropertyAccessor()),
            customFunction: new CustomFunction(),
        );

        $template = (new DocumentTemplate())
            ->setName('Шаблон для проверки полей')
            ->setCategory(BankruptcyStage::JUDICIAL_REPORT)
            ->setPath($templatePath);

        return $processor->extractFields(template: $template, contract: new Contracts())['fields'];
    }
}
