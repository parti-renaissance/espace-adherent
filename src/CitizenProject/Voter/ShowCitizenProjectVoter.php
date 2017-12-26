<?php

namespace AppBundle\CitizenProject\Voter;

use AppBundle\CitizenProject\CitizenProjectPermissions;
use AppBundle\Entity\Adherent;
use AppBundle\Entity\CitizenProject;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;

class ShowCitizenProjectVoter extends AbstractCitizenProjectVoter
{
    protected function supports($attribute, $citizenProject)
    {
        return CitizenProjectPermissions::SHOW === $attribute && $citizenProject instanceof CitizenProject;
    }

    protected function voteOnAttribute($attribute, $citizenProject, TokenInterface $token)
    {
        if ($citizenProject->isApproved()) {
            return true;
        }

        return parent::voteOnAttribute($attribute, $citizenProject, $token);
    }

    protected function doVoteOnAttribute(string $attribute, Adherent $adherent, CitizenProject $citizenProject): bool
    {
        return $citizenProject->isCreatedBy($adherent->getUuid());
    }
}
