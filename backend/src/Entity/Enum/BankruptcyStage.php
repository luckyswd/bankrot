<?php

declare(strict_types=1);

namespace App\Entity\Enum;

enum BankruptcyStage: string
{
    case BASIC_INFO = 'basic_info';
    case PRE_COURT = 'pre_court';
    case PROCEDURE_INITIATION = 'procedure_initiation';
    case PROCEDURE = 'procedure';

    public function getLabel(): string
    {
        return match ($this) {
            self::BASIC_INFO => 'Основная информация',
            self::PRE_COURT => 'Досудебка',
            self::PROCEDURE_INITIATION => 'Введение процедуры',
            self::PROCEDURE => 'Процедура',
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
