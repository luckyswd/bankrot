<?php

declare(strict_types=1);

namespace App\Tests\Service\Templates;

use App\Entity\DocumentTemplate;
use App\Entity\Enum\BankruptcyStage;
use App\Repository\DocumentTemplateRepository;
use App\Service\Templates\DocumentTemplateRegistrar;
use App\Tests\BaseTestCase;

class DocumentTemplateRegistrarTest extends BaseTestCase
{
    private const string REPORT_NAME = '2. Реестр требований кредиторов';
    private const string PUBLICATION_NAME = '1. Публикация ЕФРСБ';

    private string $templatesDir;

    protected function setUp(): void
    {
        parent::setUp();

        $this->templatesDir = sys_get_temp_dir() . '/' . uniqid('templates_registry_', true);
        mkdir($this->templatesDir, 0777, true);
    }

    protected function tearDown(): void
    {
        $this->removeDirectory(path: $this->templatesDir);

        parent::tearDown();
    }

    public function testFileWithoutRecordCreatesTemplate(): void
    {
        $path = $this->writeTemplate(category: BankruptcyStage::JUDICIAL_REPORT, file: self::REPORT_NAME . '.docx');

        $this->assertSame(['created' => 1, 'updated' => 0, 'removed' => 0], $this->createRegistrar()->register());

        $template = $this->findTemplate(name: self::REPORT_NAME, category: BankruptcyStage::JUDICIAL_REPORT);
        $this->assertNotNull($template);
        $this->assertSame($path, $template->getPath());
    }

    public function testUploadedRecordKeepsIdAndGetsRepositoryPath(): void
    {
        $uploaded = $this->createTemplate(
            name: self::PUBLICATION_NAME,
            category: BankruptcyStage::JUDICIAL_PROCEDURE_INITIATION,
            path: $this->templatesDir . '/6954eda3f29708.55739657_1. Публикация ЕФРСБ.docx',
        );
        file_put_contents($uploaded->getPath(), 'старая загрузка');
        $path = $this->writeTemplate(category: BankruptcyStage::JUDICIAL_PROCEDURE_INITIATION, file: self::PUBLICATION_NAME . '.docx');

        $result = $this->createRegistrar()->register();

        $this->assertSame(['created' => 0, 'updated' => 1, 'removed' => 0], $result);
        $template = $this->findTemplate(name: self::PUBLICATION_NAME, category: BankruptcyStage::JUDICIAL_PROCEDURE_INITIATION);
        $this->assertNotNull($template);
        $this->assertSame($uploaded->getId(), $template->getId());
        $this->assertSame($path, $template->getPath());
    }

    public function testRecordWithoutFileIsRemoved(): void
    {
        $this->createTemplate(
            name: 'Удалённый шаблон',
            category: BankruptcyStage::PRE_COURT,
            path: $this->templatesDir . '/pre_court/Удалённый шаблон.docx',
        );

        $this->assertSame(['created' => 0, 'updated' => 0, 'removed' => 1], $this->createRegistrar()->register());
        $this->assertNull($this->findTemplate(name: 'Удалённый шаблон', category: BankruptcyStage::PRE_COURT));
    }

    public function testSecondRunChangesNothing(): void
    {
        $this->writeTemplate(category: BankruptcyStage::JUDICIAL_REPORT, file: self::REPORT_NAME . '.docx');
        $this->createRegistrar()->register();

        $this->assertSame(['created' => 0, 'updated' => 0, 'removed' => 0], $this->createRegistrar()->register());
        $this->assertCount(1, $this->repository()->findAll());
    }

    public function testFilesOutsideStageFoldersAndForeignExtensionsAreSkipped(): void
    {
        file_put_contents($this->templatesDir . '/Загруженный ранее шаблон.docx', 'в корне каталога');
        $this->writeTemplate(category: BankruptcyStage::JUDICIAL_REPORT, file: 'Заметка.txt');
        $spreadsheet = $this->writeTemplate(category: BankruptcyStage::JUDICIAL_PROCEDURE_INITIATION, file: '9. Заявка на открытие спецсчета в Совкомбанк.xlsx');

        $this->assertSame(['created' => 1, 'updated' => 0, 'removed' => 0], $this->createRegistrar()->register());

        $templates = $this->repository()->findAll();
        $this->assertCount(1, $templates);
        $this->assertSame($spreadsheet, $templates[0]->getPath());
    }

    private function createRegistrar(): DocumentTemplateRegistrar
    {
        return new DocumentTemplateRegistrar(
            entityManager: self::$em,
            documentTemplateRepository: $this->repository(),
            templatesDir: $this->templatesDir,
        );
    }

    private function repository(): DocumentTemplateRepository
    {
        /** @var DocumentTemplateRepository $repository */
        $repository = self::$em->getRepository(DocumentTemplate::class);

        return $repository;
    }

    private function findTemplate(string $name, BankruptcyStage $category): ?DocumentTemplate
    {
        self::$em->clear();

        return $this->repository()->findByNameAndCategory(name: $name, category: $category);
    }

    private function createTemplate(string $name, BankruptcyStage $category, string $path): DocumentTemplate
    {
        $template = (new DocumentTemplate())->setName($name)->setCategory($category)->setPath($path);
        self::$em->persist($template);
        self::$em->flush();

        return $template;
    }

    private function writeTemplate(BankruptcyStage $category, string $file): string
    {
        $directory = $this->templatesDir . '/' . $category->value;

        if (!is_dir($directory)) {
            mkdir($directory, 0777, true);
        }

        $path = $directory . '/' . $file;
        file_put_contents($path, 'содержимое шаблона');

        return $path;
    }

    private function removeDirectory(string $path): void
    {
        if (!is_dir($path)) {
            return;
        }

        foreach (new \FilesystemIterator($path) as $file) {
            $file->isDir() ? $this->removeDirectory(path: $file->getPathname()) : unlink($file->getPathname());
        }

        rmdir($path);
    }
}
