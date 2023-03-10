<?php

namespace App\Security\Voter\Committee;

use App\Committee\CommitteeManager;
use App\Committee\CommitteePermissionEnum;
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

    protected function supports(string $attribute, $subject): bool
    {
        return \in_array($attribute, [CommitteePermissionEnum::CHANGE_MANDATE, CommitteePermissionEnum::ADD_MANDATE], true)
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

        if (CommitteePermissionEnum::ADD_MANDATE === $attribute) {
            return $this->committeeManager->hasAvailableMandateTypesFor($committee);
        }

        return true;
    }
}
