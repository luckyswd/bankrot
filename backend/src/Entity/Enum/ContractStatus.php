<?php

declare(strict_types=1);

namespace App\Entity\Enum;

enum ContractStatus: string
{
    case IN_PROGRESS = 'in_progress';
    case COMPLETED = 'completed';

    public function getLabel(): string
    {
        return match ($this) {
            self::IN_PROGRESS => 'В работе',
            self::COMPLETED => 'Завершено',
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
