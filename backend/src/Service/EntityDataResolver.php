<?php

declare(strict_types=1);

namespace App\Service;

use App\Entity\Contracts;
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
    public function resolveValue(Contracts $contract, string $path): mixed
    {
        $parts = explode('.', $path);

        if (count($parts) === 0) {
            return null;
        }

        $value = null;

        foreach ($parts as $part) {
            $camelCasePart = $this->snakeToCamel(string: $part);
            $getter = 'get' . ucfirst($camelCasePart);

            if (method_exists($contract, $getter)) {
                $value = $contract->$getter();
            } elseif ($this->propertyAccessor->isReadable(objectOrArray: $contract, propertyPath: $camelCasePart)) {
                $value = $this->propertyAccessor->getValue(objectOrArray: $contract, propertyPath: $camelCasePart);
            } else {
                return null;
            }
        }

        return $this->formatValue(value: $value);
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
