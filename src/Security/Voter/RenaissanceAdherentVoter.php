<?php

declare(strict_types=1);

namespace App\Security\Voter;

use App\Entity\Adherent;

class RenaissanceAdherentVoter extends AbstractAdherentVoter
{
    public const RENAISSANCE_ADHERENT = 'RENAISSANCE_ADHERENT';

    protected function doVoteOnAttribute(string $attribute, Adherent $adherent, $subject): bool
    {
        return $adherent->isRenaissanceAdherent();
    }

    protected function supports(string $attribute, $subject): bool
    {
        return self::RENAISSANCE_ADHERENT === $attribute && null === $subject;
    }
}
