<?php

declare(strict_types=1);

namespace App\Chatbot\RateLimit;

enum ChatbotUserTier: string
{
    case Public = 'public';
    case UserSimple = 'user_simple';
    case Sympathisant = 'sympathisant';
    case Adherent = 'adherent';
    case AdherentAJour = 'adherent_a_jour';
    case CadreLocal = 'cadre_local';
    case CadreNational = 'cadre_national';

    public function label(): string
    {
        return match ($this) {
            self::Public => 'Public',
            self::UserSimple => 'Utilisateur simple',
            self::Sympathisant => 'Sympathisant',
            self::Adherent => 'Adhérent',
            self::AdherentAJour => 'Adhérent à jour',
            self::CadreLocal => 'Cadre local',
            self::CadreNational => 'Cadre national',
        };
    }
}
