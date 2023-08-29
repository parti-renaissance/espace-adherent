<?php

namespace App\Security\Voter;

use App\Adherent\AdherentRoleEnum;
use App\Entity\Adherent;
use App\Repository\ElectedRepresentative\ElectedRepresentativeRepository;

class OnGoingElectedRepresentativeAdherentVoter extends AbstractAdherentVoter
{
    public function __construct(private readonly ElectedRepresentativeRepository $electedRepresentativeRepository)
    {
    }

    protected function supports(string $attribute, $subject): bool
    {
        return AdherentRoleEnum::ONGOING_ELECTED_REPRESENTATIVE === $attribute;
    }

    protected function doVoteOnAttribute(string $attribute, Adherent $adherent, $subject): bool
    {
        return (bool) \count($adherent->findElectedRepresentativeMandates(true));
    }
}
