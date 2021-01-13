<?php

namespace App\Security\Voter\Committee;

use App\Committee\CommitteePermissions;
use App\Entity\Adherent;
use App\Repository\ElectedRepresentative\ElectedRepresentativeRepository;
use App\Security\Voter\AbstractAdherentVoter;

class CreateCommitteeVoter extends AbstractAdherentVoter
{
    private $electedRepresentativeRepository;

    public function __construct(ElectedRepresentativeRepository $electedRepresentativeRepository)
    {
        $this->electedRepresentativeRepository = $electedRepresentativeRepository;
    }

    protected function supports($attribute, $subject)
    {
        return CommitteePermissions::CREATE === $attribute && null === $subject;
    }

    protected function doVoteOnAttribute(string $attribute, Adherent $adherent, $subject): bool
    {
        return $adherent->isReferent() || (
            !$adherent->isMinor()
            && $adherent->isCertified()
            && !$adherent->isSupervisor()
            && !$adherent->isProvisionalSupervisor()
            && !$this->electedRepresentativeRepository->hasActiveParliamentaryMandate($adherent)
        );
    }
}
