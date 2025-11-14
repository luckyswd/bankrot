<?php

declare(strict_types=1);

namespace App\Service;

use Symfony\Component\PropertyAccess\PropertyAccessorInterface;

readonly class EntityDataResolver
{
    public function __construct(
        private PropertyAccessorInterface $propertyAccessor,
    ) {
    }

    /**
     * Получает значение из контракта по пути вида "contracts.property.subProperty"
     * Первый элемент пути всегда "contracts", остальные - цепочка геттеров.
     *
     * @param object $contract Контракт для использования как базовая сущность
     * @param string $path Путь к свойству (например, "contracts.contractNumber" или "contracts.author.fio")
     *
     * @return mixed Значение свойства или null
     */
    public function resolveValue(object $contract, string $path): mixed
    {
        $parts = explode('.', $path);

        if (count($parts) === 0) {
            return null;
        }

        if ($parts[0] !== 'contracts') {
            return null;
        }

        $current = $contract;
        $remainingParts = array_slice($parts, 1);

        foreach ($remainingParts as $part) {
            if ($current === null) {
                return null;
            }

            if (!is_object($current)) {
                return null;
            }

            $camelCasePart = $this->snakeToCamel($part);
            $getter = 'get' . ucfirst($camelCasePart);

            if (method_exists($current, $getter)) {
                $current = $current->$getter();
            } elseif ($this->propertyAccessor->isReadable($current, $camelCasePart)) {
                $current = $this->propertyAccessor->getValue($current, $camelCasePart);
            } else {
                return null;
            }
        }

        return $this->formatValue($current);
    }

    /**
     * Преобразует snake_case в camelCase.
     *
     * @param string $string Строка в snake_case
     *
     * @return string Строка в camelCase
     */
    private function snakeToCamel(string $string): string
    {
        return lcfirst(str_replace('_', '', ucwords($string, '_')));
    }

    /**
     * Форматирует значение для вставки в документ.
     *
     * @param mixed $value Значение для форматирования
     *
     * @return string Отформатированное значение
     */
    private function formatValue(mixed $value): string
    {
        if ($value === null) {
            return '';
        }

        if (is_bool($value)) {
            return $value ? 'Да' : 'Нет';
        }

        if ($value instanceof \DateTimeInterface) {
            return $value->format('d.m.Y');
        }

        if (is_object($value)) {
            if (method_exists($value, '__toString')) {
                return (string)$value;
            }

            return '';
        }

        return (string)$value;
    }
}
