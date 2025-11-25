<?php

declare(strict_types=1);

namespace App\Service\Templates;

use App\Entity\Contracts;
use App\Entity\DocumentTemplate;
use Doctrine\Common\Collections\Collection;

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

        $templateProcessor = new OptimizedTemplateProcessor($outputPath);
        $templateProcessor->setMacroChars('{{', '}}');

        $variables = [];
        $functions = [];

        foreach ($templateProcessor->getVariables() as $originalVariable) {
            $cleanedVariable = $this->clearVariable(variable: $originalVariable);

            if ($this->isFunctionCall(match: $cleanedVariable)) {
                $functions[$originalVariable] = $cleanedVariable;
            } else {
                $variables[$originalVariable] = $cleanedVariable;
            }
        }

        foreach ($functions as $originalVariable => $cleanedVariable) {
            $value = $this->processFunction(functionCall: $cleanedVariable);
            $templateProcessor->setValue(search: $originalVariable, replace: $value);
        }

        foreach ($variables as $originalVariable => $cleanedVariable) {
            $value = $this->entityDataResolver->resolveValue(contract: $contract, path: $cleanedVariable);
            $templateProcessor->setValue(search: $originalVariable, replace: $value);
        }

        $templateProcessor->saveAs($outputPath);

        $this->handeFORVariables(outputPath: $outputPath, contract: $contract);

        return $outputPath;
    }

    /**
     * Очищает переменную от XML-тегов.
     *
     * @param string $variable Переменная с возможными XML-тегами
     *
     * @return string Очищенная переменная
     */
    private function clearVariable(string $variable): string
    {
        return trim(strip_tags($variable));
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
     * Обрабатывает все FOR-блоки в шаблоне (${blockName} ... ${/blockName}).
     *
     * @param string $outputPath Путь к файлу шаблона
     * @param Contracts $contract Контракт для получения данных
     *
     * @throws \RuntimeException Если блок не найден или коллекция не найдена
     */
    private function handeFORVariables(string $outputPath, Contracts $contract): void
    {
        // Используем стандартные макросы для cloneBlock
        $templateProcessor = new OptimizedTemplateProcessor(documentTemplate: $outputPath);
        $templateProcessor->setMacroChars('${', '}');

        $variables = $templateProcessor->getVariables();
        $blocks = $this->findBlocks(variables: $variables);

        foreach ($blocks as $block) {
            $collection = $this->getCollection(contract: $contract, collectionName: $block['collectionName']);

            if (!$collection || $collection->isEmpty()) {
                continue;
            }

            $blockVariables = $this->getBlockVariables(
                variables: $templateProcessor->getVariables(),
                itemName: $block['itemName']
            );

            if (empty($blockVariables)) {
                continue;
            }

            // Подготавливаем замены для всех элементов коллекции
            $variableReplacements = [];
            $index = 1;

            foreach ($collection as $item) {
                $itemReplacements = [];
                $itemName = $block['itemName'];

                foreach ($blockVariables as $originalVariable => $propertyPath) {
                    $value = $this->entityDataResolver->resolveValueFromObject(object: $item, path: $propertyPath);
                    $cleanedOriginal = trim(strip_tags($originalVariable));
                    $itemReplacements[$cleanedOriginal] = $value;
                }

                $itemReplacements['$' . $itemName . '.index'] = (string)$index;

                $variableReplacements[] = $itemReplacements;
                ++$index;
            }

            $templateProcessor->cloneBlock(
                blockName: $block['blockName'],
                clones: $collection->count(),
                variableReplacements: $variableReplacements,
            );
        }

        $templateProcessor->saveAs($outputPath);
    }

    /**
     * Находит все блоки в формате ${blockName} и ${/blockName}.
     *
     * @param array<string> $variables Массив переменных (уже без макросов ${ и })
     *
     * @return array<int, array{blockName: string, itemName: string, collectionName: string}>
     */
    private function findBlocks(array $variables): array
    {
        $blocks = [];
        $blockPattern = '/^(\w+)$/';
        $endBlockPattern = '/^\/(\w+)$/';
        $itemVariablePattern = '/^(\$)?(\w+)\./';

        $currentBlock = null;
        $itemNameMap = [];

        foreach ($variables as $variable) {
            $cleaned = trim(strip_tags($variable));

            if (preg_match($itemVariablePattern, $cleaned, $itemMatches)) {
                $itemName = $itemMatches[2];
                if ($currentBlock !== null) {
                    $itemNameMap[$currentBlock['blockName']] = $itemName;
                }
            }

            // Начало блока (переменная без точки и без слэша в начале)
            if (preg_match($blockPattern, $cleaned, $matches) && !str_starts_with($cleaned, '/') && !str_starts_with($cleaned, '$')) {
                $blockName = $matches[1];
                $currentBlock = ['blockName' => $blockName];
            }

            // Конец блока (/blockName)
            if ($currentBlock !== null && preg_match($endBlockPattern, $cleaned, $matches)) {
                $endBlockName = $matches[1];

                if ($endBlockName === $currentBlock['blockName']) {
                    // Определяем имя элемента и коллекции
                    if (isset($itemNameMap[$currentBlock['blockName']])) {
                        $itemName = $itemNameMap[$currentBlock['blockName']];
                        if (str_ends_with($itemName, 's')) {
                            $collectionName = $itemName;
                        } else {
                            $collectionName = $itemName . 's';
                        }
                    } elseif (str_ends_with($currentBlock['blockName'], 's')) {
                        $itemName = substr($currentBlock['blockName'], 0, -1);
                        $collectionName = $currentBlock['blockName'];
                    } else {
                        $itemName = $currentBlock['blockName'];
                        $collectionName = $currentBlock['blockName'] . 's';
                    }

                    $blocks[] = [
                        'blockName' => $currentBlock['blockName'],
                        'itemName' => $itemName,
                        'collectionName' => $collectionName,
                    ];

                    $currentBlock = null;
                }
            }
        }

        return $blocks;
    }

    /**
     * Получает коллекцию из контракта по имени.
     *
     * @param Contracts $contract Контракт
     * @param string $collectionName Имя коллекции (например, "creditors")
     *
     * @return Collection<int, object>|null Коллекция или null
     */
    private function getCollection(Contracts $contract, string $collectionName): ?Collection
    {
        $camelCaseName = lcfirst(str_replace('_', '', ucwords($collectionName, '_')));
        $getter = 'get' . ucfirst($camelCaseName);

        if (!method_exists($contract, $getter)) {
            return null;
        }

        $value = $contract->$getter();

        if (!$value instanceof Collection) {
            return null;
        }

        return $value;
    }

    /**
     * Получает переменные, относящиеся к блоку.
     *
     * @param array<string> $variables Все переменные шаблона
     * @param string $itemName Имя переменной элемента (например, "creditor")
     *
     * @return array<string, string> Массив [оригинальная_переменная => путь_к_свойству]
     */
    private function getBlockVariables(array $variables, string $itemName): array
    {
        $blockVariables = [];

        foreach ($variables as $originalVariable) {
            $cleaned = trim(strip_tags($originalVariable));

            // Проверяем переменные в формате $itemName.property (например, $creditor.name)
            if (preg_match('/^\$' . preg_quote($itemName, '/') . '\.(.+)$/', $cleaned, $matches)) {
                $propertyPath = $matches[1];
                $blockVariables[$originalVariable] = $propertyPath;
            }
        }

        return $blockVariables;
    }
}
