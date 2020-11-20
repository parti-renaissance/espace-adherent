<?php

namespace App\Security\Voter\TerritorialCouncil;

use App\Entity\Adherent;
use App\Entity\TerritorialCouncil\PoliticalCommitteeFeedItem;
use App\FeedItem\FeedItemPermissions;
use App\Security\Voter\AbstractAdherentVoter;

class ManagePoliticalCommitteeFeedItemVoter extends AbstractAdherentVoter
{
    /**
     * @param PoliticalCommitteeFeedItem $subject
     */
    protected function doVoteOnAttribute(string $attribute, Adherent $adherent, $subject): bool
    {
        return !$subject->isLocked()
            && $subject->getAuthor()->equals($adherent)
            && $adherent->isTerritorialCouncilPresident()
            && $adherent->isPoliticalCommitteeMember()
            && $adherent->getPoliticalCommitteeMembership()->getPoliticalCommittee() === $subject->getPoliticalCommittee()
        ;
    }

    protected function supports($attribute, $subject)
    {
        return FeedItemPermissions::CAN_MANAGE === $attribute && $subject instanceof PoliticalCommitteeFeedItem;
    }
}
