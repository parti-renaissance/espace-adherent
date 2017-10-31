<?php

namespace AppBundle\Group\Voter;

use AppBundle\Entity\Adherent;
use AppBundle\Entity\Group;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;

abstract class AbstractGroupVoter extends Voter
{
    /**
     * Votes on an attribute.
     *
     * @param string         $attribute
     * @param Group          $group
     * @param TokenInterface $token
     *
     * @return bool
     */
    protected function voteOnAttribute($attribute, $group, TokenInterface $token)
    {
        $adherent = $token->getUser();
        if (!$adherent instanceof Adherent) {
            return false;
        }

        return $this->doVoteOnAttribute($attribute, $adherent, $group);
    }

    abstract protected function doVoteOnAttribute(string $attribute, Adherent $adherent, Group $group): bool;
}
