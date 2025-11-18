<?php

declare(strict_types=1);

namespace App\Service\Templates;

class CustomFunction
{
    /**
     * Маппинг функций на методы обработки.
     *
     * @var array<string, string>
     */
    private const array FUNCTION_MAP = [
        'ТЕКУЩАЯ_ДАТА' => 'currentDate',
    ];

    /**
     * Выполняет функцию по её имени с переданными параметрами.
     *
     * @param string $functionName Имя функции
     * @param array<string> $parameters Параметры функции
     *
     * @return string Результат выполнения функции
     *
     * @throws \RuntimeException Если функция не найдена
     */
    public function execute(string $functionName, array $parameters = []): string
    {
        $functionName = mb_strtoupper($functionName);

        if (!isset(self::FUNCTION_MAP[$functionName])) {
            throw new \RuntimeException("Функция '{$functionName}' не найдена");
        }

        $methodName = self::FUNCTION_MAP[$functionName];

        if (!method_exists($this, $methodName)) {
            throw new \RuntimeException("Метод '{$methodName}' для функции '{$functionName}' не найден");
        }

        return $this->$methodName($parameters);
    }

    /**
     * Проверяет, существует ли функция.
     *
     * @param string $functionName Имя функции
     */
    public function functionExists(string $functionName): bool
    {
        $functionName = mb_strtoupper($functionName);

        return isset(self::FUNCTION_MAP[$functionName]);
    }

    /**
     * Форматирует текущую дату согласно переданному формату.
     *
     * @param array<string> $parameters Параметры: [0] - формат даты
     *
     * @return string Отформатированная дата
     */
    private function currentDate(array $parameters): string
    {
        $format = $parameters[0] ?? 'дд.ММ.гггг';
        $now = new \DateTime();

        return $this->formatDate(date: $now, format: $format);
    }

    /**
     * Форматирует дату согласно пользовательскому формату.
     *
     * @param \DateTime $date Дата для форматирования
     * @param string $format Формат даты
     *
     * @return string Отформатированная дата
     */
    private function formatDate(\DateTime $date, string $format): string
    {
        $result = $format;
        $day = (int)$date->format('d');
        $month = (int)$date->format('m');
        $year = (int)$date->format('Y');
        $yearShort = (int)$date->format('y');

        $monthNamesShort = [
            1 => 'янв.',
            2 => 'фев.',
            3 => 'мар.',
            4 => 'апр.',
            5 => 'май',
            6 => 'июн.',
            7 => 'июл.',
            8 => 'авг.',
            9 => 'сен.',
            10 => 'окт.',
            11 => 'ноя.',
            12 => 'дек.',
        ];

        $monthNamesFull = [
            1 => 'январь',
            2 => 'февраль',
            3 => 'март',
            4 => 'апрель',
            5 => 'май',
            6 => 'июнь',
            7 => 'июль',
            8 => 'август',
            9 => 'сентябрь',
            10 => 'октябрь',
            11 => 'ноябрь',
            12 => 'декабрь',
        ];

        $result = str_replace('дд', sprintf('%02d', $day), $result);
        $result = str_replace('ММММ', $monthNamesFull[$month], $result);
        $result = str_replace('МММ', $monthNamesShort[$month], $result);
        $result = str_replace('ММ', sprintf('%02d', $month), $result);
        $result = str_replace('гггг', (string)$year, $result);
        $result = str_replace('гг', sprintf('%02d', $yearShort), $result);

        return $result;
    }
}
