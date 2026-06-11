<?php

declare(strict_types=1);

namespace App\Chatbot\RateLimit;

use App\Adherent\AdherentLevel;
use App\Entity\Adherent;

class ChatbotTierResolver
{
    public function resolve(?Adherent $adherent): ChatbotUserTier
    {
        if (null === $adherent) {
            return ChatbotUserTier::Public;
        }

        if ($adherent->isCadre()) {
            return ChatbotUserTier::Cadre;
        }

        // Membership tiers are derived from the canonical Adherent::getLevel() (single source of truth).
        return match ($adherent->getLevel()) {
            AdherentLevel::ADHERENT_A_JOUR => ChatbotUserTier::AdherentAJour,
            AdherentLevel::ADHERENT => ChatbotUserTier::Adherent,
            AdherentLevel::MEMBRE => ChatbotUserTier::Sympathisant,
            AdherentLevel::USER, AdherentLevel::CONTACT => ChatbotUserTier::Contact,
        };
    }
}
