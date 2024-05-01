<?php

namespace App\Event;

enum EventVisibilityEnum: string
{
    case PUBLIC = 'public';
    case PRIVATE = 'private';
    case ADHERENT = 'adherent';
    case ADHERENT_DUES = 'adherent_dues';

    public static function isForAdherent(string $visibility): bool
    {
        return \in_array($visibility, [self::ADHERENT->value, self::ADHERENT_DUES->value]);
    }
}
