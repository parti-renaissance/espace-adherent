<?php

namespace App\Security\Voter\Committee;

use App\Committee\CommitteeAdherentMandateManager;
use App\Committee\CommitteePermissionEnum;
use App\Entity\Administrator;
use App\Entity\Committee;
use App\Security\Voter\Admin\AbstractAdminVoter;

class ManageCommitteeMandatesVoter extends AbstractAdminVoter
{
    public function __construct(private readonly CommitteeAdherentMandateManager $committeeAdherentMandateManager)
    {
    }

    protected function supports(string $attribute, $subject): bool
    {
        return \in_array($attribute, [CommitteePermissionEnum::CHANGE_MANDATE, CommitteePermissionEnum::ADD_MANDATE], true)
            && $subject instanceof Committee;
    }

    /**
     * @param Committee $committee
     */
    protected function doVoteOnAttribute(string|int $attribute, Administrator $administrator, $committee): bool
    {
        if (!$committee->isApproved()) {
            return false;
        }

        if (CommitteePermissionEnum::ADD_MANDATE === $attribute) {
            return $this->committeeAdherentMandateManager->hasAvailableMandateTypesFor($committee);
        }

        return true;
    }
}
