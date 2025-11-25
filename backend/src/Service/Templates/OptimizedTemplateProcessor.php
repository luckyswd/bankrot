<?php

declare(strict_types=1);

namespace App\Service\Templates;

use PhpOffice\PhpWord\TemplateProcessor;

/**
 * Оптимизированный TemplateProcessor с улучшенным методом cloneBlock,
 * который поддерживает блоки в таблицах.
 */
class OptimizedTemplateProcessor extends TemplateProcessor
{
    /**
     * Клонирует блок с оптимизированным поиском, включая блоки в таблицах.
     *
     * @param string $blockName Имя блока
     * @param int $clones Количество клонов
     * @param bool $replace Заменить ли оригинальный блок
     * @param bool $indexVariables Индексировать ли переменные
     * @param array<int, array<string, string>>|null $variableReplacements Замены переменных
     *
     * @return string|null XML блок или null
     */
    public function cloneBlock($blockName, $clones = 1, $replace = true, $indexVariables = false, $variableReplacements = null): ?string
    {
        $xmlBlock = null;
        $matches = [];
        $escapedMacroOpeningChars = self::$macroOpeningChars;
        $escapedMacroClosingChars = self::$macroClosingChars;

        // Оптимизированное регулярное выражение, которое ищет блоки:
        // 1. В обычных параграфах <w:p>
        // 2. В таблицах <w:tr> и <w:tc>
        // Используем более простой и эффективный паттерн
        $escapedBlockName = preg_quote($blockName, '/');
        $escapedOpening = preg_quote($escapedMacroOpeningChars, '/');
        $escapedClosing = preg_quote($escapedMacroClosingChars, '/');

        // Паттерн для начала блока: ${blockname} или {{blockname}}
        $startPattern = $escapedOpening . $escapedBlockName . $escapedClosing;

        // Паттерн для конца блока: ${/blockname} или {{/blockname}}
        $endPattern = $escapedOpening . '\/' . $escapedBlockName . $escapedClosing;

        // Оптимизированное регулярное выражение:
        // Ищем начало блока, затем любое содержимое до конца блока
        // Не ограничиваемся только <w:p> тегами - работает и в таблицах
        // Используем non-greedy поиск для нахождения ближайшего конца блока
        $pattern = '/(.*?)(' . $startPattern . ')(.*?)(' . $endPattern . ')/s';

        if (preg_match($pattern, $this->tempDocumentMainPart, $matches) === 1) {
            // $matches[1] - содержимое до начала блока
            // $matches[2] - маркер начала блока
            // $matches[3] - содержимое блока (XML блок)
            // $matches[4] - маркер конца блока
            $xmlBlock = $matches[3];

            if ($indexVariables) {
                $cloned = $this->indexClonedVariables($clones, $xmlBlock);
            } elseif ($variableReplacements !== null) {
                $cloned = $this->replaceClonedVariables($variableReplacements, $xmlBlock);
            } else {
                $cloned = [];
                for ($i = 1; $i <= $clones; ++$i) {
                    $cloned[] = $xmlBlock;
                }
            }

            if ($replace) {
                // Заменяем весь блок (включая маркеры) на клонированное содержимое
                $fullBlock = $matches[2] . $matches[3] . $matches[4];
                $this->tempDocumentMainPart = str_replace(
                    $fullBlock,
                    implode('', $cloned),
                    $this->tempDocumentMainPart
                );
            }
        }

        return $xmlBlock;
    }
}
