<?php

namespace App\Security\Voter\Committee;

use App\Committee\CommitteePermissions;
use App\Entity\Adherent;
use App\Entity\Committee;
use App\Repository\CommitteeRepository;
use App\Security\Voter\AbstractAdherentVoter;

class CreateCommitteeVoter extends AbstractAdherentVoter
{
    private $repository;

    public function __construct(CommitteeRepository $repository)
    {
        $this->repository = $repository;
    }

    protected function supports($attribute, $subject)
    {
        return CommitteePermissions::CREATE === $attribute && null === $subject;
    }

    protected function doVoteOnAttribute(string $attribute, Adherent $adherent, $subject): bool
    {
        // Cannot create a committee when referent or already host one
        return !$adherent->isReferent()
            && !$adherent->isHost()
            && !$this->repository->hasCommitteeInStatus($adherent, Committee::STATUSES_NOT_ALLOWED_TO_CREATE_ANOTHER)
        ;
    }
}
