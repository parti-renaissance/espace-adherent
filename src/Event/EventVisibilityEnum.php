<?php

namespace App\Event;

enum EventVisibilityEnum: string
{
    case PUBLIC = 'public';
    case PRIVATE = 'private';
    case ADHERENT = 'adherent';
    case ADHERENT_DUES = 'adherent_dues';
}
