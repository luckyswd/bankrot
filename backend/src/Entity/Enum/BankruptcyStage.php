<?php

declare(strict_types=1);

namespace App\Entity\Enum;

enum BankruptcyStage: string
{
    case BASIC_INFO = 'basic_info';
    case PRE_COURT = 'pre_court';
    case JUDICIAL = 'judicial';
    case REALIZATION = 'realization';
    case PROCEDURE_INITIATION = 'procedure_initiation';
    case PROCEDURE = 'procedure';
    case REPORT = 'report';

    public function getLabel(): string
    {
        return match ($this) {
            self::BASIC_INFO => 'Основная информация',
            self::PRE_COURT => 'Досудебка',
            self::JUDICIAL => 'Судебка',
            self::REALIZATION => 'Реализация',
            self::PROCEDURE_INITIATION => 'Введение процедуры',
            self::PROCEDURE => 'Процедура',
            self::REPORT => 'Отчет',
        };
    }

    /**
     * @return array<string>
     */
    public static function getValues(): array
    {
        return array_column(self::cases(), 'value');
    }
}
