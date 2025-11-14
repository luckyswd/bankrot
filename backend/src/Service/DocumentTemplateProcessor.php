<?php

declare(strict_types=1);

namespace App\Service;

use App\Entity\Contracts;
use App\Entity\DocumentTemplate;

class DocumentTemplateProcessor
{
    public function __construct(
        private readonly EntityDataResolver $entityDataResolver,
    ) {
    }

    /**
     * Обрабатывает шаблон документа, заменяя переменные на данные из контракта.
     *
     * @param DocumentTemplate $template Шаблон документа
     * @param Contracts $contract Контракт для подстановки данных
     *
     * @return string Путь к обработанному файлу
     *
     * @throws \Exception
     */
    public function processTemplate(DocumentTemplate $template, Contracts $contract): string
    {
        $templatePath = $template->getPath();

        if (!file_exists($templatePath)) {
            throw new \RuntimeException('Файл шаблона не найден: ' . $templatePath);
        }

        $outputPath = sys_get_temp_dir() . '/' . uniqid('doc_', true) . '.docx';

        copy($templatePath, $outputPath);

        $zip = new \ZipArchive();

        if ($zip->open($outputPath) !== true) {
            throw new \RuntimeException('Не удалось открыть DOCX файл');
        }

        $variables = $this->extractVariables(zip: $zip);

        foreach ($variables as $variable) {
            $value = $this->entityDataResolver->resolveValue(contract: $contract, path: $variable);
            $this->replaceVariableInZip(zip: $zip, variable: $variable, value: $value);
        }

        $zip->close();

        return $outputPath;
    }

    /**
     * Извлекает все переменные из DOCX файла.
     *
     * @param \ZipArchive $zip ZIP архив DOCX файла
     *
     * @return array<string> Массив переменных
     */
    private function extractVariables(\ZipArchive $zip): array
    {
        $variables = [];
        $xmlFiles = ['word/document.xml', 'word/header1.xml', 'word/footer1.xml'];

        for ($i = 0; $i < $zip->numFiles; ++$i) {
            $filename = $zip->getNameIndex($i);

            if ($filename === false) {
                continue;
            }

            $isRelevantFile = false;

            foreach ($xmlFiles as $xmlFile) {
                if (str_contains($filename, $xmlFile) || (str_ends_with($filename, '.xml') && str_starts_with($filename, 'word/'))) {
                    $isRelevantFile = true;

                    break;
                }
            }

            if (!$isRelevantFile) {
                continue;
            }

            $content = $zip->getFromIndex($i);

            if ($content === false) {
                continue;
            }

            preg_match_all('/\{\{([^}]+)\}\}/', $content, $matches);

            if (!empty($matches[1])) {
                foreach ($matches[1] as $match) {
                    $varName = trim($match);
                    if (!in_array($varName, $variables, true)) {
                        $variables[] = $varName;
                    }
                }
            }
        }

        return $variables;
    }

    /**
     * Заменяет переменную в DOCX файле, сохраняя стили.
     *
     * @param \ZipArchive $zip ZIP архив DOCX файла
     * @param string $variable Имя переменной
     * @param string $value Значение для замены
     */
    private function replaceVariableInZip(\ZipArchive $zip, string $variable, string $value): void
    {
        $pattern = '/\{\{' . preg_quote($variable, '/') . '\}\}/';
        $xmlFiles = ['word/document.xml', 'word/header1.xml', 'word/footer1.xml'];

        for ($i = 0; $i < $zip->numFiles; ++$i) {
            $filename = $zip->getNameIndex($i);

            if ($filename === false) {
                continue;
            }

            $isRelevantFile = false;

            foreach ($xmlFiles as $xmlFile) {
                if (str_contains($filename, $xmlFile) || (str_ends_with($filename, '.xml') && str_starts_with($filename, 'word/'))) {
                    $isRelevantFile = true;
                    break;
                }
            }

            if (!$isRelevantFile) {
                continue;
            }

            $content = $zip->getFromIndex($i);

            if ($content === false) {
                continue;
            }

            if (preg_match($pattern, $content)) {
                $escapedValue = htmlspecialchars($value, ENT_XML1, 'UTF-8');
                $newContent = preg_replace($pattern, $escapedValue, $content);
                $zip->deleteIndex($i);
                $zip->addFromString($filename, $newContent);
            }
        }
    }
}
