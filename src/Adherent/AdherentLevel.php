<?php

declare(strict_types=1);

namespace App\Adherent;

enum AdherentLevel: string
{
    case CONTACT = 'contact';
    case USER = 'user';
    case MEMBRE = 'membre';
    case ADHERENT = 'adherent';
    case ADHERENT_A_JOUR = 'adherent_a_jour';

    public function role(): string
    {
        return match ($this) {
            self::CONTACT, self::USER => 'ROLE_USER',
            self::MEMBRE => 'ROLE_MEMBRE',
            self::ADHERENT => 'ROLE_ADHERENT',
            self::ADHERENT_A_JOUR => 'ROLE_ADHERENT_A_JOUR',
        };
    }
}
