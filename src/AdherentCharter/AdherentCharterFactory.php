<?php

declare(strict_types=1);

namespace App\AdherentCharter;

use App\Entity\AdherentCharter\AdherentCharterInterface;
use App\Entity\AdherentCharter\CandidateCharter;
use App\Entity\AdherentCharter\CommitteeHostCharter;
use App\Entity\AdherentCharter\DeputyCharter;
use App\Entity\AdherentCharter\LegislativeCandidateCharter;
use App\Entity\AdherentCharter\PapCampaignCharter;
use App\Entity\AdherentCharter\PhoningCampaignCharter;

abstract class AdherentCharterFactory
{
    public static function create(string $type): ?AdherentCharterInterface
    {
        switch ($type) {
            case AdherentCharterTypeEnum::TYPE_COMMITTEE_HOST:
                return new CommitteeHostCharter();
            case AdherentCharterTypeEnum::TYPE_DEPUTY:
                return new DeputyCharter();
            case AdherentCharterTypeEnum::TYPE_LEGISLATIVE_CANDIDATE:
                return new LegislativeCandidateCharter();
            case AdherentCharterTypeEnum::TYPE_CANDIDATE:
                return new CandidateCharter();
            case AdherentCharterTypeEnum::TYPE_PHONING_CAMPAIGN:
                return new PhoningCampaignCharter();
            case AdherentCharterTypeEnum::TYPE_PAP_CAMPAIGN:
                return new PapCampaignCharter();
        }

        return null;
    }
}
