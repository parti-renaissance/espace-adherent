<?php

declare(strict_types=1);

namespace App\Entity\Event;

enum RegistrationStatusEnum: string
{
    case INVITED = 'invited';
    case CONFIRMED = 'confirmed';
}
