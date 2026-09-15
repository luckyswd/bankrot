<?php

declare(strict_types=1);

namespace App\Service\Templates;

use Symfony\Component\DependencyInjection\Attribute\Autowire;

readonly class DocumentTemplateSynchronizer
{
    public function __construct(
        #[Autowire(param: 'app.document_templates_dir')]
        private string $templatesDir,
        #[Autowire(param: 'app.document_templates_bundle_dir')]
        private string $bundleDir,
        #[Autowire(param: 'app.legacy_document_templates_dir')]
        private string $legacyDir,
    ) {
    }

    /**
     * @return array{bundled: int, migrated: int}
     */
    public function synchronize(): array
    {
        if (!is_dir($this->templatesDir) && !mkdir($this->templatesDir, 0775, true) && !is_dir($this->templatesDir)) {
            throw new \RuntimeException(sprintf('Не удалось создать каталог шаблонов %s', $this->templatesDir));
        }

        return [
            'bundled' => $this->copyFiles(sourceDir: $this->bundleDir, overwrite: true),
            'migrated' => $this->copyFiles(sourceDir: $this->legacyDir, overwrite: false),
        ];
    }

    private function copyFiles(string $sourceDir, bool $overwrite): int
    {
        if (!is_dir($sourceDir) || realpath($sourceDir) === realpath($this->templatesDir)) {
            return 0;
        }

        $copied = 0;

        foreach (new \FilesystemIterator($sourceDir) as $file) {
            if (!$file instanceof \SplFileInfo || !$file->isFile() || str_starts_with($file->getFilename(), '.')) {
                continue;
            }

            $target = $this->templatesDir . '/' . $file->getFilename();

            if (!$overwrite && file_exists($target)) {
                continue;
            }

            if (!copy($file->getPathname(), $target)) {
                throw new \RuntimeException(sprintf('Не удалось скопировать шаблон %s', $file->getPathname()));
            }

            ++$copied;
        }

        return $copied;
    }
}
