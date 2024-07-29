<?php

namespace App\Security\Voter\Committee;

use App\Committee\CommitteePermissionEnum;
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

    protected function supports(string $attribute, $subject): bool
    {
        return CommitteePermissionEnum::CREATE === $attribute && null === $subject;
    }

    protected function doVoteOnAttribute(string $attribute, Adherent $adherent, $subject): bool
    {
        return
            !$adherent->isMinor()
            && $adherent->isCertified()
            && !$adherent->isSupervisor()
            && !$adherent->isProvisionalSupervisor()
            && !$this->electedRepresentativeRepository->hasActiveParliamentaryMandate($adherent);
    }
}
