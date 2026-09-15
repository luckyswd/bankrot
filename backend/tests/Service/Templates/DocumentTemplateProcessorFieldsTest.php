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
    private string $templatePath;

    protected function setUp(): void
    {
        $this->templatePath = sys_get_temp_dir() . '/' . uniqid('report_fields_', true) . '.docx';

        $phpWord = new PhpWord();
        $section = $phpWord->addSection();
        $section->addText('Дата закрытия реестра кредиторов {{ registryClosingDateText }}');
        $section->addText('за должником{{ spouseInstrumental }} автотранспортных средствах');

        IOFactory::createWriter($phpWord, 'Word2007')->save($this->templatePath);
    }

    protected function tearDown(): void
    {
        if (file_exists($this->templatePath)) {
            unlink($this->templatePath);
        }
    }

    public function testFieldsInsideReportMethodsAreDetected(): void
    {
        $processor = new DocumentTemplateProcessor(
            entityDataResolver: new EntityDataResolver(propertyAccessor: PropertyAccess::createPropertyAccessor()),
            customFunction: new CustomFunction(),
        );

        $template = (new DocumentTemplate())
            ->setName('1. Отчёт финансового управляющего')
            ->setCategory(BankruptcyStage::JUDICIAL_REPORT)
            ->setPath($this->templatePath);

        $fields = $processor->extractFields(template: $template, contract: new Contracts())['fields'];

        $this->assertContains('judicial_procedure_initiation.procedureInitiationKommersantPublicationDate', $fields);
        $this->assertContains('basic_info.maritalStatus', $fields);
        $this->assertContains('basic_info.gender', $fields);
    }
}
