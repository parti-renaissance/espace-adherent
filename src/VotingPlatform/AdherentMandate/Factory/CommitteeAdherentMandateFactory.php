<?php

namespace App\VotingPlatform\AdherentMandate\Factory;

use App\Admin\Committee\CommitteeAdherentMandateTypeEnum;
use App\Entity\AdherentMandate\AdherentMandateInterface;
use App\Entity\AdherentMandate\CommitteeAdherentMandate;
use App\Entity\VotingPlatform\Candidate;
use App\Entity\VotingPlatform\Election;
use App\VotingPlatform\Designation\DesignationTypeEnum;

class CommitteeAdherentMandateFactory implements AdherentMandateFactoryInterface
{
    public function create(Election $election, Candidate $candidate, string $quality): AdherentMandateInterface
    {
        $mandate = new CommitteeAdherentMandate(
            $candidate->getAdherent(),
            $candidate->getGender(),
            $election->getVoteEndDate(),
            null,
            DesignationTypeEnum::COMMITTEE_SUPERVISOR === $election->getDesignationType() ?
                CommitteeAdherentMandateTypeEnum::TYPE_SUPERVISOR :
                null
        );

        $mandate->setCommittee($election->getElectionEntity()->getCommittee());

        return $mandate;
    }

    public function supports(Election $election): bool
    {
        return $election->getDesignation()->isCommitteeType();
    }
}
