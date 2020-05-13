<?php

namespace App\Security\Voter\Committee;

use App\Committee\CommitteePermissions;
use App\Entity\Adherent;
use App\Entity\CommitteeFeedItem;
use App\Security\Voter\AbstractAdherentVoter;

class AdministrateCommitteeFeedItemVoter extends AbstractAdherentVoter
{
    protected function supports($attribute, $subject)
    {
        return CommitteePermissions::ADMIN_FEED === $attribute
            && $subject instanceof CommitteeFeedItem;
    }

    /**
     * @param CommitteeFeedItem $subject
     */
    protected function doVoteOnAttribute(string $attribute, Adherent $adherent, $subject): bool
    {
        return $subject->getAuthor()->equals($adherent) || $adherent->isSupervisorOf($subject->getCommittee());
    }
}
