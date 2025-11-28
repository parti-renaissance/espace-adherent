<?php

declare(strict_types=1);

namespace App\Adherent\Unregistration;

enum TypeEnum: string
{
    case SYMPATHIZER = 'sympathizer';
    case ADHERENT = 'adherent';
}
