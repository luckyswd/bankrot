<?php

declare(strict_types=1);

namespace App\Entity\Enum;

enum InsuranceStatus: string
{
    case VALID = 'valid';
    case EXPIRED = 'expired';
    case MISSING = 'missing';
}
