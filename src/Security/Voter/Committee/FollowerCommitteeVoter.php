<?php

declare(strict_types=1);

namespace App\Security\Voter\Committee;

use App\Committee\CommitteePermissionEnum;
use App\Entity\Adherent;
use App\Entity\Committee;
use App\Repository\AdherentRepository;
use App\Security\Voter\AbstractAdherentVoter;

class FollowerCommitteeVoter extends AbstractAdherentVoter
{
    private $adherentRepository;

    public function __construct(AdherentRepository $adherentRepository)
    {
        $this->adherentRepository = $adherentRepository;
    }

    protected function supports(string $attribute, $subject): bool
    {
        return $subject instanceof Committee
            && \in_array($attribute, CommitteePermissionEnum::FOLLOWER, true);
    }

    /**
     * @param Committee $committee
     */
    protected function doVoteOnAttribute(string $attribute, Adherent $adherent, $committee): bool
    {
        if (!$committee->isApproved()) {
            return false;
        }

        $membership = $adherent->getMembershipFor($committee);

        if (CommitteePermissionEnum::FOLLOW === $attribute) {
            return !$membership;
        }

        // A supervisor cannot unfollow its committee
        if (!$membership || $membership->isSupervisor()) {
            return false;
        }

        if ($membership->getCommitteeCandidacy($election = $committee->getCommitteeElection())) {
            return false;
        }

        if ($election && $election->isOngoing() && ($election->isCandidacyPeriodActive() || $election->countConfirmedCandidacies())) {
            return false;
        }

        // Any basic follower of a committee can unfollow the committee at any point in time.
        // A host can only if another host is registered for that committee.
        return $membership->isFollower() || 1 < $this->adherentRepository->countCommitteeHosts($committee);
    }
}
