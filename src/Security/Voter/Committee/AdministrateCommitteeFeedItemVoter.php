<?php

namespace AppBundle\Security\Voter\Committee;

use AppBundle\Committee\CommitteePermissions;
use AppBundle\Entity\Adherent;
use AppBundle\Entity\CommitteeFeedItem;
use AppBundle\Security\Voter\AbstractAdherentVoter;

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
