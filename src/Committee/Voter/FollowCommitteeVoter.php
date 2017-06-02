<?php

namespace AppBundle\Committee\Voter;

use AppBundle\Committee\CommitteeManager;
use AppBundle\Committee\CommitteePermissions;
use AppBundle\Entity\Adherent;
use AppBundle\Entity\Committee;

class FollowCommitteeVoter extends AbstractCommitteeVoter
{
    private $manager;

    public function __construct(CommitteeManager $manager)
    {
        $this->manager = $manager;
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
            return $this->voteOnFollowCommitteeAttribute($adherent, $committee);
        }

        return $this->voteOnUnfollowCommitteeAttribute($adherent, $committee);
    }

    private function voteOnFollowCommitteeAttribute(Adherent $adherent, Committee $committee): bool
    {
        return !$this->manager->isFollowingCommittee($adherent, $committee);
    }

    private function voteOnUnfollowCommitteeAttribute(Adherent $adherent, Committee $committee): bool
    {
        // An adherent who isn't already following (or hosting) a committee
        // cannot unfollow (or leave) it.
        if (!$membership = $this->manager->getCommitteeMembership($adherent, $committee)) {
            return false;
        }

        // Any basic follower of a committee can unfollow the committee
        // at any point in time.
        return $membership->isFollower() || 1 < $this->manager->countCommitteeHosts($committee);
    }
}
