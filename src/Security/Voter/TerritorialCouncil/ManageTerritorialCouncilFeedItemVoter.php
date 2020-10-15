<?php

namespace App\Security\Voter\TerritorialCouncil;

use App\Entity\Adherent;
use App\Entity\TerritorialCouncil\TerritorialCouncilFeedItem;
use App\FeedItem\FeedItemPermissions;
use App\Security\Voter\AbstractAdherentVoter;

class ManageTerritorialCouncilFeedItemVoter extends AbstractAdherentVoter
{
    /**
     * @param TerritorialCouncilFeedItem $subject
     */
    protected function doVoteOnAttribute(string $attribute, Adherent $adherent, $subject): bool
    {
        return !$subject->isLocked()
            && $subject->getAuthor()->equals($adherent)
            && $adherent->isTerritorialCouncilPresident()
            && $adherent->getTerritorialCouncilMembership()->getTerritorialCouncil() === $subject->getTerritorialCouncil()
        ;
    }

    protected function supports($attribute, $subject)
    {
        return FeedItemPermissions::CAN_MANAGE === $attribute && $subject instanceof TerritorialCouncilFeedItem;
    }
}
