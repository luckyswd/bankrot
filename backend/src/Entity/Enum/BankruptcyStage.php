<?php

declare(strict_types=1);

namespace App\Entity\Enum;

enum BankruptcyStage: string
{
    case BASIC_INFO = 'basic_info';
    case PRE_COURT = 'pre_court';
    case JUDICIAL_PROCEDURE_INITIATION = 'judicial_procedure_initiation';
    case JUDICIAL_PROCEDURE = 'judicial_procedure';
    case JUDICIAL_REPORT = 'judicial_report';

    public function getLabel(): string
    {
        return match ($this) {
            self::BASIC_INFO => 'Основная информация',
            self::PRE_COURT => 'Досудебка',
            self::JUDICIAL_PROCEDURE_INITIATION => 'Судебка введение процедуры',
            self::JUDICIAL_PROCEDURE => 'Судебка процедура',
            self::JUDICIAL_REPORT => 'Судебка отчет',
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
