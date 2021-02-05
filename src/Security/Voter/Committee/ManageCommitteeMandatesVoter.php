<?php

namespace App\Security\Voter\Committee;

use App\Committee\CommitteeManager;
use App\Committee\CommitteePermissions;
use App\Entity\Administrator;
use App\Entity\Committee;
use App\Security\Voter\Admin\AbstractAdminVoter;

class ManageCommitteeMandatesVoter extends AbstractAdminVoter
{
    private $committeeManager;

    public function __construct(CommitteeManager $committeeManager)
    {
        $this->committeeManager = $committeeManager;
    }

    protected function supports($attribute, $subject): bool
    {
        return \in_array($attribute, [CommitteePermissions::CHANGE_MANDATE, CommitteePermissions::ADD_MANDATE], true)
            && $subject instanceof Committee;
    }

    /**
     * @param Committee $committee
     */
    protected function doVoteOnAttribute(string $attribute, Administrator $administrator, $committee): bool
    {
        if (!$committee->isApproved()) {
            return false;
        }

        if (CommitteePermissions::ADD_MANDATE === $attribute) {
            return $this->committeeManager->hasAvailableMandateTypesFor($committee);
        }

        return true;
    }
}
