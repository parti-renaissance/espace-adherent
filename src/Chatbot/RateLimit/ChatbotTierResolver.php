<?php

declare(strict_types=1);

namespace App\Chatbot\RateLimit;

use App\Adherent\Tag\TagEnum;
use App\Entity\Adherent;

class ChatbotTierResolver
{
    private const CADRE_NATIONAL_ROLES = [
        'ROLE_NATIONAL',
        'ROLE_DEPUTY',
        'ROLE_SENATOR',
        'ROLE_FDE_COORDINATOR',
    ];

    private const CADRE_LOCAL_ROLES = [
        'ROLE_REGIONAL_COORDINATOR',
        'ROLE_REGIONAL_DELEGATE',
        'ROLE_PRESIDENT_DEPARTMENTAL_ASSEMBLY',
        'ROLE_HOST',
        'ROLE_ANIMATOR',
    ];

    public function resolve(?Adherent $adherent): ChatbotUserTier
    {
        if (null === $adherent) {
            return ChatbotUserTier::Public;
        }

        if ($this->hasAnyRole($adherent, self::CADRE_NATIONAL_ROLES)) {
            return ChatbotUserTier::CadreNational;
        }

        if ($this->hasAnyRole($adherent, self::CADRE_LOCAL_ROLES)) {
            return ChatbotUserTier::CadreLocal;
        }

        if ($adherent->hasTag(TagEnum::getAdherentYearTag())) {
            return ChatbotUserTier::AdherentAJour;
        }

        if ($adherent->hasTag(TagEnum::ADHERENT)) {
            return ChatbotUserTier::Adherent;
        }

        if ($adherent->hasTag(TagEnum::SYMPATHISANT)) {
            return ChatbotUserTier::Sympathisant;
        }

        return ChatbotUserTier::UserSimple;
    }

    /**
     * @param string[] $roles
     */
    private function hasAnyRole(Adherent $adherent, array $roles): bool
    {
        $adherentRoles = $adherent->getRoles();

        foreach ($roles as $role) {
            if (\in_array($role, $adherentRoles, true)) {
                return true;
            }
        }

        return false;
    }
}
