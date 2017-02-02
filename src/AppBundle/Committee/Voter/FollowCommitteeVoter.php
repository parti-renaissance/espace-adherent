<?php

namespace AppBundle\Committee\Voter;

use AppBundle\Committee\CommitteePermissions;
use AppBundle\Entity\Adherent;
use AppBundle\Entity\Committee;
use AppBundle\Repository\CommitteeMembershipRepository;

class FollowCommitteeVoter extends AbstractCommitteeVoter
{
    private $repository;

    public function __construct(CommitteeMembershipRepository $repository)
    {
        $this->repository = $repository;
    }

    protected function supports($attribute, $subject)
    {
        $attributes = [
            CommitteePermissions::FOLLOW,
            CommitteePermissions::UNFOLLOW,
        ];

        return in_array(strtoupper($attribute), $attributes, true) && $subject instanceof Committee;
    }

    protected function doVoteOnAttribute(string $attribute, Adherent $adherent, Committee $committee): bool
    {
        if (!$committee->isApproved()) {
            return false;
        }

        if (CommitteePermissions::FOLLOW === $attribute) {
            return !$this->repository->isMemberOf($adherent, (string) $committee->getUuid());
        }

        return $this->voteOnUnfollowCommitteeAttribute($adherent, $committee);
    }

    private function voteOnUnfollowCommitteeAttribute(Adherent $adherent, Committee $committee): bool
    {
        $committeeUuid = (string) $committee->getUuid();

        // An adherent who isn't already following (or hosting) a committee
        // cannot unfollow (or leave) it.
        if (!$membership = $this->repository->findMembership($adherent, $committeeUuid)) {
            return false;
        }

        // Any basic follower of a committee can unfollow the committee
        // at any point in time.
        if (!$membership->isHostMember()) {
            return true;
        }

        // If an adherent is granted a host privilege on a committee,
        // then he\she can leave it only if there is at least another
        // host person to manage this committee. A committee cannot be
        // managed by zero hosts members.
        return $this->repository->countHostMembers($committeeUuid) >= 2;
    }
}
