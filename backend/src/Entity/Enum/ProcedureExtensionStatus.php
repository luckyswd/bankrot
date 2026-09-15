<?php

declare(strict_types=1);

namespace App\Entity\Enum;

enum ProcedureExtensionStatus: string
{
    case EXTENDED = 'extended';
    case NOT_EXTENDED = 'not_extended';
    case NO_ACTS = 'no_acts';
}
