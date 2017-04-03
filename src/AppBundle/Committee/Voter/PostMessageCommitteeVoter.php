<?php

namespace AppBundle\Committee\Voter;

use AppBundle\Committee\CommitteeManager;
use AppBundle\Committee\CommitteePermissions;
use AppBundle\Entity\Adherent;
use AppBundle\Entity\Committee;

class PostMessageCommitteeVoter extends AbstractCommitteeVoter
{
    private $manager;

    public function __construct(CommitteeManager $manager)
    {
        $this->manager = $manager;
    }

    protected function supports($attribute, $committee)
    {
        return CommitteePermissions::POST_MESSAGE === $attribute && $committee instanceof Committee;
    }

    protected function doVoteOnAttribute(string $attribute, Adherent $adherent, Committee $committee): bool
    {
        return $committee->isApproved() && $this->manager->hostCommittee($adherent, $committee);
    }
}
