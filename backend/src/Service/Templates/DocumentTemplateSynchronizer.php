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
        $this->ensureDirectory(path: $this->templatesDir);

        return [
            'bundled' => $this->copyTree(sourceDir: $this->bundleDir),
            'migrated' => $this->copyFlat(sourceDir: $this->legacyDir),
        ];
    }

    private function copyTree(string $sourceDir): int
    {
        if (!$this->isCopyableSource(sourceDir: $sourceDir)) {
            return 0;
        }

        $copied = 0;
        $iterator = new \RecursiveIteratorIterator(
            new \RecursiveDirectoryIterator($sourceDir, \FilesystemIterator::SKIP_DOTS),
            \RecursiveIteratorIterator::SELF_FIRST,
        );

        foreach ($iterator as $file) {
            if (!$file instanceof \SplFileInfo || str_starts_with($file->getFilename(), '.')) {
                continue;
            }

            $target = $this->templatesDir . '/' . ltrim(substr($file->getPathname(), strlen($sourceDir)), '/');

            if ($file->isDir()) {
                $this->ensureDirectory(path: $target);

                continue;
            }

            $this->copyFile(source: $file->getPathname(), target: $target);
            ++$copied;
        }

        return $copied;
    }

    private function copyFlat(string $sourceDir): int
    {
        if (!$this->isCopyableSource(sourceDir: $sourceDir)) {
            return 0;
        }

        $copied = 0;

        foreach (new \FilesystemIterator($sourceDir) as $file) {
            if (!$file instanceof \SplFileInfo || !$file->isFile() || str_starts_with($file->getFilename(), '.')) {
                continue;
            }

            $target = $this->templatesDir . '/' . $file->getFilename();

            if (file_exists($target)) {
                continue;
            }

            $this->copyFile(source: $file->getPathname(), target: $target);
            ++$copied;
        }

        return $copied;
    }

    private function isCopyableSource(string $sourceDir): bool
    {
        return is_dir($sourceDir) && realpath($sourceDir) !== realpath($this->templatesDir);
    }

    private function copyFile(string $source, string $target): void
    {
        $this->ensureDirectory(path: dirname($target));

        if (!copy($source, $target)) {
            throw new \RuntimeException(sprintf('Не удалось скопировать шаблон %s', $source));
        }
    }

    private function ensureDirectory(string $path): void
    {
        if (!is_dir($path) && !mkdir($path, 0775, true) && !is_dir($path)) {
            throw new \RuntimeException(sprintf('Не удалось создать каталог шаблонов %s', $path));
        }
    }
}
