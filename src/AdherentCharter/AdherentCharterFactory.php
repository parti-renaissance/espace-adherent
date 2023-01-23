<?php

namespace App\AdherentCharter;

use App\Entity\AdherentCharter\AdherentCharterInterface;
use App\Entity\AdherentCharter\CandidateCharter;
use App\Entity\AdherentCharter\CommitteeHostCharter;
use App\Entity\AdherentCharter\DeputyCharter;
use App\Entity\AdherentCharter\LegislativeCandidateCharter;
use App\Entity\AdherentCharter\LreCharter;
use App\Entity\AdherentCharter\PapCampaignCharter;
use App\Entity\AdherentCharter\PhoningCampaignCharter;
use App\Entity\AdherentCharter\ReferentCharter;
use App\Entity\AdherentCharter\SenatorCharter;
use App\Entity\AdherentCharter\SenatorialCandidateCharter;
use App\Entity\AdherentCharter\ThematicCommunityChiefCharter;

abstract class AdherentCharterFactory
{
    public static function create(string $type): ?AdherentCharterInterface
    {
        switch ($type) {
            case AdherentCharterTypeEnum::TYPE_COMMITTEE_HOST:
                return new CommitteeHostCharter();
            case AdherentCharterTypeEnum::TYPE_REFERENT:
                return new ReferentCharter();
            case AdherentCharterTypeEnum::TYPE_DEPUTY:
                return new DeputyCharter();
            case AdherentCharterTypeEnum::TYPE_SENATOR:
                return new SenatorCharter();
            case AdherentCharterTypeEnum::TYPE_SENATORIAL_CANDIDATE:
                return new SenatorialCandidateCharter();
            case AdherentCharterTypeEnum::TYPE_LRE:
                return new LreCharter();
            case AdherentCharterTypeEnum::TYPE_LEGISLATIVE_CANDIDATE:
                return new LegislativeCandidateCharter();
            case AdherentCharterTypeEnum::TYPE_CANDIDATE:
                return new CandidateCharter();
            case AdherentCharterTypeEnum::TYPE_THEMATIC_COMMUNITY_CHIEF:
                return new ThematicCommunityChiefCharter();
            case AdherentCharterTypeEnum::TYPE_PHONING_CAMPAIGN:
                return new PhoningCampaignCharter();
            case AdherentCharterTypeEnum::TYPE_PAP_CAMPAIGN:
                return new PapCampaignCharter();
        }

        return null;
    }
}
