<?php

namespace AppBundle\Committee\Voter;

use AppBundle\Entity\Adherent;
use AppBundle\Entity\Committee;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;

abstract class AbstractCommitteeVoter extends Voter
{
    /**
     * Votes on an attribute.
     *
     * @param string         $attribute
     * @param Committee      $committee
     * @param TokenInterface $token
     *
     * @return bool
     */
    protected function voteOnAttribute($attribute, $committee, TokenInterface $token)
    {
        $adherent = $token->getUser();
        if (!$adherent instanceof Adherent) {
            return false;
        }

        return $this->doVoteOnAttribute($attribute, $adherent, $committee);
    }

    abstract protected function doVoteOnAttribute(string $attribute, Adherent $adherent, Committee $committee): bool;
}
