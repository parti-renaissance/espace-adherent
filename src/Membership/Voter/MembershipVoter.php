<?php

namespace AppBundle\Membership\Voter;

use AppBundle\Membership\MembershipPermissions;
use AppBundle\Entity\Adherent;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;

class MembershipVoter extends Voter
{
    protected function supports($attribute, $subject)
    {
        return MembershipPermissions::UNREGISTER === $attribute && null === $subject;
    }

    protected function voteOnAttribute($attribute, $subject, TokenInterface $token)
    {
        $adherent = $token->getUser();

        if (!$adherent instanceof Adherent) {
            return false;
        }

        if (!$adherent->isBasicAdherent()) {
            return false;
        }

        return true;
    }
}
