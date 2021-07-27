<?php

namespace App\Audience;

use App\Entity\Adherent;
use App\Entity\Audience\CandidateAudience;
use App\Entity\Audience\DeputyAudience;
use App\Entity\Audience\ReferentAudience;
use App\Entity\Audience\SenatorAudience;
use App\Geo\ManagedZoneProvider;

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
                return ManagedZoneProvider::REFERENT;
            case DeputyAudience::class:
                return ManagedZoneProvider::DEPUTY;
            case SenatorAudience::class:
                return ManagedZoneProvider::SENATOR;
            case CandidateAudience::class:
                return ManagedZoneProvider::CANDIDATE;
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
