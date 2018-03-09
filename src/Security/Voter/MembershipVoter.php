<?php

namespace AppBundle\Security\Voter;

use AppBundle\Membership\MembershipPermissions;
use AppBundle\Entity\Adherent;

class MembershipVoter extends AbstractAdherentVoter
{
    /**
     * {@inheritdoc}
     */
    protected function supports($attribute, $subject)
    {
        return MembershipPermissions::UNREGISTER === $attribute && null === $subject;
    }

    protected function doVoteOnAttribute(string $attribute, Adherent $adherent, $subject): bool
    {
        if ($adherent->isBasicAdherent()) {
            return true;
        }

        if ($adherent->isUser()) {
            return true;
        }

        return false;
    }
}
