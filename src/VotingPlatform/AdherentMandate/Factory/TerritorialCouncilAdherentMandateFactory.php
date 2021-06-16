<?php

namespace App\VotingPlatform\AdherentMandate\Factory;

use App\Entity\AdherentMandate\AdherentMandateInterface;
use App\Entity\AdherentMandate\TerritorialCouncilAdherentMandate;
use App\Entity\VotingPlatform\Candidate;
use App\Entity\VotingPlatform\Election;
use App\VotingPlatform\Designation\DesignationTypeEnum;

class TerritorialCouncilAdherentMandateFactory implements AdherentMandateFactoryInterface
{
    public function create(Election $election, Candidate $candidate, string $quality): AdherentMandateInterface
    {
        return TerritorialCouncilAdherentMandate::create(
            $election->getElectionEntity()->getTerritorialCouncil(),
            $candidate->getAdherent(),
            $election->getVoteEndDate(),
            $candidate->getGender(),
            $quality
        );
    }

    public function supports(Election $election): bool
    {
        return DesignationTypeEnum::COPOL === $election->getDesignationType();
    }
}
