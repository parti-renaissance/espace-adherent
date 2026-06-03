<?php

declare(strict_types=1);

namespace App\Chatbot\RateLimit;

enum ChatbotUserTier: string
{
    case Public = 'public';
    case Contact = 'contact';
    case Sympathisant = 'sympathisant';
    case Adherent = 'adherent';
    case AdherentAJour = 'adherent_a_jour';
    case Cadre = 'cadre';

    public function label(): string
    {
        return match ($this) {
            self::Public => 'Public',
            self::Contact => 'Contact',
            self::Sympathisant => 'Sympathisant',
            self::Adherent => 'Adhérent',
            self::AdherentAJour => 'Adhérent à jour',
            self::Cadre => 'Cadre',
        };
    }
}
