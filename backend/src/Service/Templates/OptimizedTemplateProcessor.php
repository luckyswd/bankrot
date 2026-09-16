<?php

declare(strict_types=1);

namespace App\Service\Templates;

use PhpOffice\PhpWord\TemplateProcessor;

class OptimizedTemplateProcessor extends TemplateProcessor
{
    private const string ROW_OPENING_PATTERN = '/<w:tr[\s>]/';
    private const string ROW_CLOSING_TAG = '</w:tr>';

    /**
     * @param string $blockName
     * @param int $clones
     * @param bool $replace
     * @param bool $indexVariables
     * @param array<int, array<string, string>>|null $variableReplacements
     */
    public function cloneBlock($blockName, $clones = 1, $replace = true, $indexVariables = false, $variableReplacements = null): ?string
    {
        $startMarker = self::$macroOpeningChars . $blockName . self::$macroClosingChars;
        $endMarker = self::$macroOpeningChars . '/' . $blockName . self::$macroClosingChars;

        $startOffset = strpos($this->tempDocumentMainPart, $startMarker);

        if ($startOffset === false) {
            return null;
        }

        $endOffset = strpos($this->tempDocumentMainPart, $endMarker, $startOffset + strlen($startMarker));

        if ($endOffset === false) {
            return null;
        }

        $row = $this->findEnclosingRow(startOffset: $startOffset, endOffset: $endOffset);

        if ($row !== null) {
            [$rowStart, $rowLength] = $row;
            $xmlBlock = str_replace(
                [$startMarker, $endMarker],
                '',
                substr($this->tempDocumentMainPart, $rowStart, $rowLength),
            );

            if ($replace) {
                $this->tempDocumentMainPart = substr_replace(
                    $this->tempDocumentMainPart,
                    implode('', $this->cloneXmlBlock(
                        xmlBlock: $xmlBlock,
                        clones: $clones,
                        indexVariables: $indexVariables,
                        variableReplacements: $variableReplacements,
                    )),
                    $rowStart,
                    $rowLength,
                );
            }

            return $xmlBlock;
        }

        $blockStart = $startOffset + strlen($startMarker);
        $xmlBlock = substr($this->tempDocumentMainPart, $blockStart, $endOffset - $blockStart);

        if ($replace) {
            $this->tempDocumentMainPart = str_replace(
                $startMarker . $xmlBlock . $endMarker,
                implode('', $this->cloneXmlBlock(
                    xmlBlock: $xmlBlock,
                    clones: $clones,
                    indexVariables: $indexVariables,
                    variableReplacements: $variableReplacements,
                )),
                $this->tempDocumentMainPart,
            );
        }

        return $xmlBlock;
    }

    /**
     * @return array<int, string>
     */
    public function getMainPartVariableOccurrences(): array
    {
        return $this->getVariablesForPart($this->tempDocumentMainPart);
    }

    /**
     * @return array{int, int}|null
     */
    private function findEnclosingRow(int $startOffset, int $endOffset): ?array
    {
        $beforeStart = substr($this->tempDocumentMainPart, 0, $startOffset);

        if (preg_match_all(self::ROW_OPENING_PATTERN, $beforeStart, $rowOpenings, PREG_OFFSET_CAPTURE) === 0) {
            return null;
        }

        $rowStart = $rowOpenings[0][array_key_last($rowOpenings[0])][1];

        if (str_contains(substr($beforeStart, $rowStart), self::ROW_CLOSING_TAG)) {
            return null;
        }

        $rowClosing = strpos($this->tempDocumentMainPart, self::ROW_CLOSING_TAG, $startOffset);

        if ($rowClosing === false || $endOffset > $rowClosing) {
            return null;
        }

        return [$rowStart, $rowClosing + strlen(self::ROW_CLOSING_TAG) - $rowStart];
    }

    /**
     * @param array<int, array<string, string>>|null $variableReplacements
     *
     * @return array<int, string>
     */
    private function cloneXmlBlock(string $xmlBlock, int $clones, bool $indexVariables, ?array $variableReplacements): array
    {
        if ($indexVariables) {
            return $this->indexClonedVariables($clones, $xmlBlock);
        }

        if ($variableReplacements !== null) {
            return $this->replaceClonedVariables($variableReplacements, $xmlBlock);
        }

        return array_fill(0, max($clones, 0), $xmlBlock);
    }
}
