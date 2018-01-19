<?php

namespace AppBundle\Security\Voter;

use AppBundle\Membership\MembershipPermissions;
use AppBundle\Entity\Adherent;

class MembershipVoter extends AbstractAdherentVoter
{
    protected function supports($attribute, $subject)
    {
        return MembershipPermissions::UNREGISTER === $attribute && null === $subject;
    }

    protected function doVoteOnAttribute(string $attribute, Adherent $adherent, $subject): bool
    {
        return $adherent->isBasicAdherent();
    }
}
