<?php

namespace App\Entity\Event;

enum RegistrationStatusEnum: string
{
    case INVITED = 'invited';
    case CONFIRMED = 'confirmed';
}
