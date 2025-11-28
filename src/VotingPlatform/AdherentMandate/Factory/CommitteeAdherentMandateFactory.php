<?php

declare(strict_types=1);

namespace App\VotingPlatform\AdherentMandate\Factory;

use App\Entity\AdherentMandate\AdherentMandateInterface;
use App\Entity\AdherentMandate\CommitteeAdherentMandate;
use App\Entity\VotingPlatform\Candidate;
use App\Entity\VotingPlatform\Election;

class CommitteeAdherentMandateFactory implements AdherentMandateFactoryInterface
{
    public function create(Election $election, Candidate $candidate, string $quality): AdherentMandateInterface
    {
        $mandate = new CommitteeAdherentMandate(
            $candidate->getAdherent(),
            $candidate->getGender(),
            $election->getVoteEndDate()
        );

        $mandate->setCommittee($election->getElectionEntity()->getCommittee());

        return $mandate;
    }

    public function supports(Election $election): bool
    {
        return $election->getDesignation()->isCommitteeAdherentType();
    }
}
