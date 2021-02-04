<?php

namespace App\Security\Voter\Committee;

use App\Committee\CommitteeManager;
use App\Committee\CommitteePermissions;
use App\Entity\Adherent;
use App\Entity\Committee;
use App\Security\Voter\AbstractAdherentVoter;

class PromoteToHostInCommitteeVoter extends AbstractAdherentVoter
{
    /** @var CommitteeManager */
    private $committeeManager;

    public function __construct(CommitteeManager $committeeManager)
    {
        $this->committeeManager = $committeeManager;
    }

    protected function supports($attribute, $subject): bool
    {
        return CommitteePermissions::PROMOTE_TO_HOST === $attribute && $subject instanceof Committee;
    }

    /**
     * @param Committee $committee
     */
    protected function doVoteOnAttribute(string $attribute, Adherent $adherent, $committee): bool
    {
        return $this->committeeManager->countCommitteeHosts($committee, true) < 2;
    }
}
