<?php

namespace AppBundle\CitizenProject\Voter;

use AppBundle\Entity\Adherent;
use AppBundle\Entity\CitizenProject;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;

abstract class AbstractCitizenProjectVoter extends Voter
{
    /**
     * Votes on an attribute.
     *
     * @param string         $attribute
     * @param CitizenProject $citizenProject
     * @param TokenInterface $token
     *
     * @return bool
     */
    protected function voteOnAttribute($attribute, $citizenProject, TokenInterface $token)
    {
        $adherent = $token->getUser();
        if (!$adherent instanceof Adherent) {
            return false;
        }

        return $this->doVoteOnAttribute($attribute, $adherent, $citizenProject);
    }

    abstract protected function doVoteOnAttribute(string $attribute, Adherent $adherent, CitizenProject $citizenProject): bool;
}
