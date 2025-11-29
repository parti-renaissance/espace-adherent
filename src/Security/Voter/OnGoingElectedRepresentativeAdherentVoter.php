<?php

declare(strict_types=1);

namespace App\Security\Voter;

use App\Adherent\AdherentRoleEnum;
use App\Entity\Adherent;

class OnGoingElectedRepresentativeAdherentVoter extends AbstractAdherentVoter
{
    protected function supports(string $attribute, $subject): bool
    {
        return AdherentRoleEnum::ONGOING_ELECTED_REPRESENTATIVE === $attribute;
    }

    protected function doVoteOnAttribute(string $attribute, Adherent $adherent, $subject): bool
    {
        return (bool) \count($adherent->findElectedRepresentativeMandates(true));
    }
}
