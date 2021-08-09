<?php

namespace App\Audience;

use App\AdherentSpace\AdherentSpaceEnum;
use App\Entity\Adherent;
use App\Entity\Audience\CandidateAudience;
use App\Entity\Audience\DeputyAudience;
use App\Entity\Audience\ReferentAudience;
use App\Entity\Audience\SenatorAudience;

class AudienceHelper
{
    public static function getAudienceClassName(string $type): string
    {
        if (!isset(AudienceTypeEnum::CLASSES[$type])) {
            throw new \InvalidArgumentException(sprintf('Message type "%s" is undefined', $type));
        }

        return AudienceTypeEnum::CLASSES[$type];
    }

    public static function getSpaceType(string $audienceClass): ?string
    {
        switch ($audienceClass) {
            case ReferentAudience::class:
                return AdherentSpaceEnum::REFERENT;
            case DeputyAudience::class:
                return AdherentSpaceEnum::DEPUTY;
            case SenatorAudience::class:
                return AdherentSpaceEnum::SENATOR;
            case CandidateAudience::class:
                return AdherentSpaceEnum::CANDIDATE;
            default:
                return null;
        }
    }

    public static function validateAdherentAccess(Adherent $adherent, string $audienceClass): bool
    {
        if (ReferentAudience::class === $audienceClass && $adherent->isReferent()) {
            return true;
        } elseif (DeputyAudience::class === $audienceClass && $adherent->isDeputy()) {
            return true;
        } elseif (SenatorAudience::class === $audienceClass && $adherent->isSenator()) {
            return true;
        } elseif (CandidateAudience::class === $audienceClass && $adherent->isHeadedRegionalCandidate()) {
            return true;
        } else {
            return false;
        }
    }
}
