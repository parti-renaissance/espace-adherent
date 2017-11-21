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

        return true;
    }

    private function voteOnFollowCitizenProjectAttribute(Adherent $adherent, CitizenProject $citizenProject): bool
    {
        return !$this->manager->isFollowingCitizenProject($adherent, $citizenProject);
    }
}
