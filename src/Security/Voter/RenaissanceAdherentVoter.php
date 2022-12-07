<?php

namespace App\Security\Voter;

use App\Entity\Adherent;

class RenaissanceAdherentVoter extends AbstractAdherentVoter
{
    public const ROLE_RENAISSANCE_ADHERENT = 'ROLE_RENAISSANCE_ADHERENT';

    protected function doVoteOnAttribute(string $attribute, Adherent $adherent, $subject): bool
    {
        return $adherent->isRenaissanceAdherent();
    }

    protected function supports(string $attribute, $subject): bool
    {
        return self::ROLE_RENAISSANCE_ADHERENT === $attribute;
    }
}
