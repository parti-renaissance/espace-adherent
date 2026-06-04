<?php

declare(strict_types=1);

namespace App\Chatbot\RateLimit;

use App\Entity\Adherent;

class ChatbotTierResolver
{
    private const string DELEGATED_ROLE_PREFIX = 'ROLE_DELEGATED_';

    /** @var string[] */
    private const array CADRE_ROLES = [
        'ROLE_NATIONAL',
        'ROLE_DEPUTY',
        'ROLE_SENATOR',
        'ROLE_FDE_COORDINATOR',
        'ROLE_REGIONAL_COORDINATOR',
        'ROLE_REGIONAL_DELEGATE',
        'ROLE_PRESIDENT_DEPARTMENTAL_ASSEMBLY',
        'ROLE_HOST',
        'ROLE_SUPERVISOR',
        'ROLE_ANIMATOR',
        'ROLE_CORRESPONDENT',
        'ROLE_PROCURATION_MANAGER',
        'ROLE_JECOUTE_MANAGER',
    ];

    public function resolve(?Adherent $adherent): ChatbotUserTier
    {
        if (null === $adherent) {
            return ChatbotUserTier::Public;
        }

        if ($this->isCadre($adherent)) {
            return ChatbotUserTier::Cadre;
        }

        if ($adherent->hasActiveMembership()) {
            return ChatbotUserTier::AdherentAJour;
        }

        if ($adherent->isRenaissanceAdherent()) {
            return ChatbotUserTier::Adherent;
        }

        if ($adherent->isRenaissanceSympathizer()) {
            return ChatbotUserTier::Sympathisant;
        }

        return ChatbotUserTier::Contact;
    }

    private function isCadre(Adherent $adherent): bool
    {
        foreach ($adherent->getRoles() as $role) {
            if (\in_array($role, self::CADRE_ROLES, true) || str_starts_with($role, self::DELEGATED_ROLE_PREFIX)) {
                return true;
            }
        }

        return false;
    }
}
