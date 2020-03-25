<?php

namespace AppBundle\Security\Voter;

use AppBundle\Entity\Adherent;

class MembershipVoter extends AbstractAdherentVoter
{
    public const PERMISSION_UNREGISTER = 'UNREGISTER';

    protected function supports($attribute, $subject)
    {
        return self::PERMISSION_UNREGISTER === $attribute && null === $subject;
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
