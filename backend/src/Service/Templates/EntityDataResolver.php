<?php

declare(strict_types=1);

namespace App\Service\Templates;

use App\Entity\Contracts;
use Symfony\Component\PropertyAccess\PropertyAccessorInterface;

readonly class EntityDataResolver
{
    public const string DEFAULT_VALUE = 'НЕТ_ДАННЫХ_В_БД';

    public function __construct(
        private PropertyAccessorInterface $propertyAccessor,
    ) {
    }

    /**
     * Получает значение из контракта по пути вида "court.shortName" или "court.property.subProperty"
     * Если элементов 2+, то первые N-1 - цепочка связей, последний - свойство/геттер финального объекта.
     *
     * @param Contracts $contract Контракт для использования как базовая сущность
     * @param string $path Путь к свойству (например, "court.shortName" или "court.something.else")
     *
     * @return string Значение свойства или null
     */
    public function resolveValue(Contracts $contract, string $path): string
    {
        $parts = explode('.', $path);

        if (count($parts) === 0) {
            return self::DEFAULT_VALUE;
        }

        $currentObject = $contract;
        $partsCount = count($parts);

        // Если один элемент - это свойство контракта
        if ($partsCount === 1) {
            $value = $this->formatValue(
                value: $this->getPropertyValue(object: $currentObject, propertyName: $parts[0])
            );

            return empty($value) ? self::DEFAULT_VALUE : $value;
        }

        // Если 2+ элемента: первые N-1 - цепочка связей, последний - свойство
        // Проходим по цепочке связей (все кроме последнего элемента)
        for ($i = 0; $i < $partsCount - 1; ++$i) {
            $value = $this->getPropertyValue(object: $currentObject, propertyName: $parts[$i]);

            if (!$value || !is_object($value)) {
                return self::DEFAULT_VALUE;
            }

            $currentObject = $value;
        }

        // Последний элемент - свойство/геттер финального объекта
        $finalValue = $this->getPropertyValue(object: $currentObject, propertyName: $parts[$partsCount - 1]);

        return $this->formatValue(value: $finalValue);
    }

    /**
     * Получает значение свойства объекта через геттер или PropertyAccessor.
     *
     * @param object $object Объект для получения свойства
     * @param string $propertyName Имя свойства (может быть в snake_case)
     *
     * @return mixed Значение свойства или null
     */
    private function getPropertyValue(object $object, string $propertyName): mixed
    {
        $camelCasePart = $this->snakeToCamel(string: $propertyName);
        $getter = 'get' . ucfirst($camelCasePart);

        if (method_exists($object, $getter)) {
            return $object->$getter();
        }

        if ($this->propertyAccessor->isReadable(objectOrArray: $object, propertyPath: $camelCasePart)) {
            return $this->propertyAccessor->getValue(objectOrArray: $object, propertyPath: $camelCasePart);
        }

        return null;
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
