<?php

namespace App\Security\Voter\Event;

use App\Entity\Adherent;
use App\Entity\Event\Event;
use App\Security\Voter\AbstractAdherentVoter;

class HostEventVoter extends AbstractAdherentVoter
{
    protected function supports(string $attribute, $subject): bool
    {
        return 'HOST_EVENT' === $attribute && $subject instanceof Event;
    }

    /**
     * @param Event $event
     */
    protected function doVoteOnAttribute(string $attribute, Adherent $adherent, $event): bool
    {
        if ($event->getOrganizer() && $adherent->equals($event->getOrganizer())) {
            return true;
        }

        if ($committee = $event->getCommittee()) {
            return $adherent->isSupervisorOf($committee) || $adherent->isHostOf($committee);
        }

        return false;
    }
}
