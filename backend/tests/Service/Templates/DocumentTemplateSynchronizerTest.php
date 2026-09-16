<?php

declare(strict_types=1);

namespace App\Tests\Service\Templates;

use App\Service\Templates\DocumentTemplateSynchronizer;
use PHPUnit\Framework\TestCase;

class DocumentTemplateSynchronizerTest extends TestCase
{
    private string $root;

    protected function setUp(): void
    {
        $this->root = sys_get_temp_dir() . '/' . uniqid('templates_sync_', true);

        foreach (['templates', 'bundle', 'legacy'] as $directory) {
            mkdir($this->root . '/' . $directory, 0777, true);
        }
    }

    protected function tearDown(): void
    {
        $this->removeDirectory(path: $this->root);
    }

    public function testBundledTemplatesOverwriteAndLegacyUploadsAreMovedWithoutOverwrite(): void
    {
        $this->write(directory: 'bundle', file: 'judicial_procedure_initiation/1. Публикация ЕФРСБ.docx', content: 'из репозитория');
        $this->write(directory: 'templates', file: 'judicial_procedure_initiation/1. Публикация ЕФРСБ.docx', content: 'устаревшая копия');
        $this->write(directory: 'bundle', file: 'judicial_report/2. Реестр требований кредиторов.docx', content: 'новый шаблон');
        $this->write(directory: 'legacy', file: 'uploaded_2. Заявка.docx', content: 'загружено на проде');
        $this->write(directory: 'legacy', file: 'uploaded_3. Уведомление.docx', content: 'ещё одна загрузка');
        $this->write(directory: 'templates', file: 'uploaded_3. Уведомление.docx', content: 'уже перенесено');
        $this->write(directory: 'legacy', file: '.DS_Store', content: 'служебный файл');

        $result = $this->createSynchronizer()->synchronize();

        $this->assertSame(['bundled' => 2, 'migrated' => 1], $result);
        $this->assertSame('из репозитория', $this->read(file: 'judicial_procedure_initiation/1. Публикация ЕФРСБ.docx'));
        $this->assertSame('новый шаблон', $this->read(file: 'judicial_report/2. Реестр требований кредиторов.docx'));
        $this->assertSame('загружено на проде', $this->read(file: 'uploaded_2. Заявка.docx'));
        $this->assertSame('уже перенесено', $this->read(file: 'uploaded_3. Уведомление.docx'));
        $this->assertFileDoesNotExist($this->root . '/templates/.DS_Store');
    }

    public function testMissingSourceDirectoriesAreSkipped(): void
    {
        $synchronizer = new DocumentTemplateSynchronizer(
            templatesDir: $this->root . '/templates',
            bundleDir: $this->root . '/absent-bundle',
            legacyDir: $this->root . '/absent-legacy',
        );

        $this->assertSame(['bundled' => 0, 'migrated' => 0], $synchronizer->synchronize());
    }

    private function createSynchronizer(): DocumentTemplateSynchronizer
    {
        return new DocumentTemplateSynchronizer(
            templatesDir: $this->root . '/templates',
            bundleDir: $this->root . '/bundle',
            legacyDir: $this->root . '/legacy',
        );
    }

    private function write(string $directory, string $file, string $content): void
    {
        $path = $this->root . '/' . $directory . '/' . $file;

        if (!is_dir(dirname($path))) {
            mkdir(dirname($path), 0777, true);
        }

        file_put_contents($path, $content);
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

    private function read(string $file): string
    {
        return (string)file_get_contents($this->root . '/templates/' . $file);
    }
}
