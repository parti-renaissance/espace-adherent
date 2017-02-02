<?php

namespace AppBundle\Committee\Voter;

use AppBundle\Committee\CommitteePermissions;
use AppBundle\Entity\Adherent;
use AppBundle\Entity\Committee;
use AppBundle\Repository\CommitteeMembershipRepository;

class HostCommitteeVoter extends AbstractCommitteeVoter
{
    private $repository;

    public function __construct(CommitteeMembershipRepository $repository)
    {
        $this->repository = $repository;
    }

    protected function supports($attribute, $committee)
    {
        return CommitteePermissions::HOST === $attribute && $committee instanceof Committee;
    }

    protected function doVoteOnAttribute(string $attribute, Adherent $adherent, Committee $committee): bool
    {
        return $this->repository->hostCommittee($adherent, (string) $committee->getUuid());
    }
}
