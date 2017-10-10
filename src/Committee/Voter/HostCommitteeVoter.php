<?php

namespace AppBundle\Committee\Voter;

use AppBundle\Committee\CommitteeManager;
use AppBundle\Committee\CommitteePermissions;
use AppBundle\Entity\Adherent;
use AppBundle\Entity\Committee;

class HostCommitteeVoter extends AbstractCommitteeVoter
{
    private $manager;

    public function __construct(CommitteeManager $manager)
    {
        $this->manager = $manager;
    }

    protected function supports($attribute, $committee)
    {
        return CommitteePermissions::HOST === $attribute && $committee instanceof Committee;
    }

    protected function doVoteOnAttribute(string $attribute, Adherent $adherent, Committee $committee): bool
    {
        if (!$committee->isApproved()) {
            return $this->manager->superviseCommittee($adherent, $committee)
                || $adherent->getUuid()->toString() === $committee->getCreatedBy();
        }

        return $this->manager->hostCommittee($adherent, $committee);
    }
}
