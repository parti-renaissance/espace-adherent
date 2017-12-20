<?php

namespace AppBundle\CitizenProject\Voter;

use AppBundle\CitizenProject\CitizenProjectPermissions;
use AppBundle\CitizenProject\CitizenProjectManager;
use AppBundle\Entity\Adherent;
use AppBundle\Entity\CitizenProject;

class FollowCitizenProjectVoter extends AbstractCitizenProjectVoter
{
    private $manager;

    public function __construct(CitizenProjectManager $manager)
    {
        $this->manager = $manager;
    }

    protected function supports($attribute, $subject)
    {
        $attributes = [
            CitizenProjectPermissions::FOLLOW,
            CitizenProjectPermissions::UNFOLLOW,
        ];

        return in_array(strtoupper($attribute), $attributes, true) && $subject instanceof CitizenProject;
    }

    protected function doVoteOnAttribute(string $attribute, Adherent $adherent, CitizenProject $citizenProject): bool
    {
        if (!$citizenProject->isApproved()) {
            return false;
        }

        if (CitizenProjectPermissions::FOLLOW === $attribute) {
            return $this->voteOnFollowCitizenProjectAttribute($adherent, $citizenProject);
        }

        return $this->voteOnUnfollowCitizenProjectAttribute($adherent, $citizenProject);
    }

    private function voteOnFollowCitizenProjectAttribute(Adherent $adherent, CitizenProject $citizenProject): bool
    {
        return !$this->manager->isFollowingCitizenProject($adherent, $citizenProject);
    }

    private function voteOnUnfollowCitizenProjectAttribute(Adherent $adherent, CitizenProject $citizenProject): bool
    {
        // An adherent who isn't already following (or hosting) cannot unfollow (or leave) it
        if (!$membership = $this->manager->getCitizenProjectMembership($adherent, $citizenProject)) {
            return false;
        }

        // Administrator cannot unfollow (or leave)
        if ($membership->isAdministrator()) {
            return false;
        }

        return $membership->isFollower();
    }
}
