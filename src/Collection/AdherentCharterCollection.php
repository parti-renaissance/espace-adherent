<?php

declare(strict_types=1);

namespace App\Collection;

use App\AdherentCharter\AdherentCharterTypeEnum;
use App\Entity\AdherentCharter\AdherentCharterInterface;
use App\Entity\AdherentCharter\CandidateCharter;
use App\Entity\AdherentCharter\CommitteeHostCharter;
use App\Entity\AdherentCharter\DeputyCharter;
use App\Entity\AdherentCharter\LegislativeCandidateCharter;
use App\Entity\AdherentCharter\PapCampaignCharter;
use App\Entity\AdherentCharter\PhoningCampaignCharter;
use Doctrine\Common\Collections\ArrayCollection;

class AdherentCharterCollection extends ArrayCollection
{
    public function hasCommitteeHostCharterAccepted(): bool
    {
        return $this->exists(static function (int $index, AdherentCharterInterface $charter) {
            return $charter instanceof CommitteeHostCharter;
        });
    }

    public function hasDeputyCharterAccepted(): bool
    {
        return $this->exists(static function (int $index, AdherentCharterInterface $charter) {
            return $charter instanceof DeputyCharter;
        });
    }

    public function hasLegislativeCandidateCharterAccepted(): bool
    {
        return $this->exists(static function (int $index, AdherentCharterInterface $charter) {
            return $charter instanceof LegislativeCandidateCharter;
        });
    }

    public function hasCandidateCharterAccepted(): bool
    {
        return $this->exists(static function (int $index, AdherentCharterInterface $charter) {
            return $charter instanceof CandidateCharter;
        });
    }

    public function hasPhoningCampaignCharterAccepted(): bool
    {
        return $this->exists(static function (int $index, AdherentCharterInterface $charter) {
            return $charter instanceof PhoningCampaignCharter;
        });
    }

    public function hasPapCampaignCharterAccepted(): bool
    {
        return $this->exists(static function (int $index, AdherentCharterInterface $charter) {
            return $charter instanceof PapCampaignCharter;
        });
    }

    public function hasCharterAcceptedForType(string $type): bool
    {
        switch ($type) {
            case AdherentCharterTypeEnum::TYPE_COMMITTEE_HOST:
                return $this->hasCommitteeHostCharterAccepted();

            case AdherentCharterTypeEnum::TYPE_DEPUTY:
                return $this->hasDeputyCharterAccepted();

            case AdherentCharterTypeEnum::TYPE_LEGISLATIVE_CANDIDATE:
                return $this->hasLegislativeCandidateCharterAccepted();

            case AdherentCharterTypeEnum::TYPE_CANDIDATE:
                return $this->hasCandidateCharterAccepted();

            case AdherentCharterTypeEnum::TYPE_PHONING_CAMPAIGN:
                return $this->hasPhoningCampaignCharterAccepted();

            case AdherentCharterTypeEnum::TYPE_PAP_CAMPAIGN:
                return $this->hasPapCampaignCharterAccepted();
        }

        return false;
    }
}
