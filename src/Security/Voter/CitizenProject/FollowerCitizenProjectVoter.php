<?php

namespace App\Security\Voter\CitizenProject;

use App\CitizenProject\CitizenProjectPermissions;
use App\Entity\Adherent;
use App\Entity\CitizenProject;
use App\Security\Voter\AbstractAdherentVoter;

class FollowerCitizenProjectVoter extends AbstractAdherentVoter
{
    protected function supports($attribute, $subject)
    {
        return $subject instanceof CitizenProject
            && \in_array($attribute, CitizenProjectPermissions::FOLLOWER, true)
        ;
    }

    /**
     * @param CitizenProject $citizenProject
     */
    protected function doVoteOnAttribute(string $attribute, ?Adherent $adherent, $citizenProject): bool
    {
        if (!$citizenProject->isApproved()) {
            return false;
        }

        $membership = $adherent->getCitizenProjectMembershipFor($citizenProject);

        if (CitizenProjectPermissions::FOLLOW === $attribute) {
            return !$membership;
        }

        // Administrator cannot unfollow (or leave)
        if (!$membership || $membership->isAdministrator()) {
            return false;
        }

        return $membership->isFollower();
    }
}
