<?php

declare(strict_types=1);

namespace App\Service;

use App\Entity\Contracts;
use App\Entity\DocumentTemplate;
use App\Service\Templates\CustomFunction;

readonly class DocumentTemplateProcessor
{
    public function __construct(
        private EntityDataResolver $entityDataResolver,
        private CustomFunction $customFunction,
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
        $functions = $this->extractFunctions(zip: $zip);

        foreach ($variables as $variable) {
            $value = $this->entityDataResolver->resolveValue(contract: $contract, path: $variable);
            $this->replaceVariableInZip(zip: $zip, variable: $variable, value: $value);
        }

        foreach ($functions as $functionCall) {
            $value = $this->processFunction(functionCall: $functionCall);
            $this->replaceVariableInZip(zip: $zip, variable: $functionCall, value: $value);
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
                    $normalized = html_entity_decode($varName, ENT_QUOTES | ENT_XML1, 'UTF-8');

                    if ($this->isFunctionCall(match: $normalized)) {
                        continue;
                    }

                    if (!in_array($normalized, $variables, true)) {
                        $variables[] = $normalized;
                    }
                }
            }
        }

        return $variables;
    }

    /**
     * Извлекает все функции из DOCX файла.
     *
     * @param \ZipArchive $zip ZIP архив DOCX файла
     *
     * @return array<string> Массив вызовов функций
     */
    private function extractFunctions(\ZipArchive $zip): array
    {
        $functions = [];
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
                    $match = trim($match);
                    $normalized = html_entity_decode($match, ENT_QUOTES | ENT_XML1, 'UTF-8');

                    if ($this->isFunctionCall(match: $normalized)) {
                        if (!in_array($normalized, $functions, true)) {
                            $functions[] = $normalized;
                        }
                    }
                }
            }
        }

        return $functions;
    }

    /**
     * Проверяет, является ли строка вызовом функции.
     *
     * @param string $match Строка для проверки
     */
    private function isFunctionCall(string $match): bool
    {
        $normalized = html_entity_decode($match, ENT_QUOTES | ENT_XML1, 'UTF-8');

        return preg_match('/^[А-ЯЁ_]+\(/', $normalized) === 1;
    }

    /**
     * Обрабатывает вызов функции и возвращает результат.
     *
     * @param string $functionCall Полный вызов функции, например: ТЕКУЩАЯ_ДАТА('дд.ММ.гггг')
     *
     * @return string Результат выполнения функции
     *
     * @throws \RuntimeException Если функция не найдена или произошла ошибка
     */
    private function processFunction(string $functionCall): string
    {
        $parsed = $this->parseFunctionCall(functionCall: $functionCall);

        if ($parsed === null) {
            throw new \RuntimeException("Не удалось распарсить вызов функции: {$functionCall}");
        }

        [$functionName, $parameters] = $parsed;

        return $this->customFunction->execute(functionName: $functionName, parameters: $parameters);
    }

    /**
     * Парсит вызов функции и извлекает имя функции и параметры.
     *
     * @param string $functionCall Полный вызов функции, например: ТЕКУЩАЯ_ДАТА('дд.ММ.гггг')
     *
     * @return array{0: string, 1: array<string>}|null Массив [имя_функции, [параметры]] или null при ошибке
     */
    private function parseFunctionCall(string $functionCall): ?array
    {
        if (preg_match('/^([А-ЯЁ_]+)\((.*)\)$/', $functionCall, $matches) !== 1) {
            return null;
        }

        $functionName = $matches[1];
        $paramsString = trim($matches[2]);

        if ($paramsString === '') {
            return [$functionName, []];
        }

        $parameters = $this->parseParameters(paramsString: $paramsString);

        return [$functionName, $parameters];
    }

    /**
     * Парсит строку параметров функции.
     *
     * @param string $paramsString Строка параметров, например: 'дд.ММ.гггг', 'параметр2'
     *
     * @return array<string> Массив параметров
     */
    private function parseParameters(string $paramsString): array
    {
        $parameters = [];
        $currentParam = '';
        $inQuotes = false;
        $quoteChar = null;
        $length = mb_strlen($paramsString);

        for ($i = 0; $i < $length; ++$i) {
            $char = mb_substr($paramsString, $i, 1);

            if (($char === "'" || $char === '"') && ($i === 0 || mb_substr($paramsString, $i - 1, 1) !== '\\')) {
                if (!$inQuotes) {
                    $inQuotes = true;
                    $quoteChar = $char;
                } elseif ($char === $quoteChar) {
                    $inQuotes = false;
                    $quoteChar = null;
                } else {
                    $currentParam .= $char;
                }
            } elseif ($char === ',' && !$inQuotes) {
                $parameters[] = trim($currentParam, " \t\n\r\0\x0B'\"");
                $currentParam = '';
            } else {
                $currentParam .= $char;
            }
        }

        if ($currentParam !== '') {
            $parameters[] = trim($currentParam, " \t\n\r\0\x0B'\"");
        }

        return $parameters;
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

            $escapedVariable = preg_quote($variable, '/');
            $escapedVariableHtml = preg_quote(htmlspecialchars($variable, ENT_QUOTES | ENT_XML1, 'UTF-8'), '/');
            $pattern = '/\{\{(' . $escapedVariable . '|' . $escapedVariableHtml . ')\}\}/';

            if (preg_match($pattern, $content)) {
                $escapedValue = htmlspecialchars($value, ENT_XML1, 'UTF-8');
                $newContent = preg_replace($pattern, $escapedValue, $content);
                $zip->deleteIndex($i);
                $zip->addFromString($filename, $newContent);
            }
        }
    }
}
