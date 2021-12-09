<?php

namespace App\Security\Voter;

use App\Entity\Adherent;

class PapUserVoter extends AbstractAdherentVoter
{
    public const PERMISSION = 'IS_PAP_USER';

    protected function supports($attribute, $subject)
    {
        return self::PERMISSION === $attribute;
    }

    protected function doVoteOnAttribute(string $attribute, Adherent $user, $subject): bool
    {
        return $user->hasPapUserRole();
    }
}
