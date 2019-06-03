<?php

namespace AppBundle\Security\Voter;

use AppBundle\Entity\Adherent;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;

abstract class AbstractAdherentVoter extends Voter
{
    protected function voteOnAttribute($attribute, $subject, TokenInterface $token)
    {
        $adherent = $token->getUser();
        if (!$adherent instanceof Adherent) {
            return false;
        }

        return $this->doVoteOnAttribute($attribute, $adherent, $subject);
    }

    abstract protected function doVoteOnAttribute(string $attribute, Adherent $adherent, $subject): bool;
}
