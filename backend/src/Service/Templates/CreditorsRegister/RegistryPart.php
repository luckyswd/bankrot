<?php

declare(strict_types=1);

namespace App\Service\Templates\CreditorsRegister;

enum RegistryPart: string
{
    case MAIN = 'main';
    case SANCTION = 'sanction';
}
