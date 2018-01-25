<?php

namespace AppBundle\Security\Voter;

use AppBundle\Entity\Adherent;
use AppBundle\Entity\Event;
use AppBundle\Event\EventPermissions;

class HostEventVoter extends AbstractAdherentVoter
{
    /**
     * {@inheritdoc}
     */
    protected function supports($attribute, $event)
    {
        return EventPermissions::HOST === $attribute && $event instanceof Event;
    }

    /**
     * @param string   $attribute
     * @param Adherent $adherent
     * @param Event    $event
     *
     * @return bool
     */
    protected function doVoteOnAttribute(string $attribute, Adherent $adherent, $event): bool
    {
        if ($event->getOrganizer() && $adherent->equals($event->getOrganizer())) {
            return true;
        }

        if (!$committee = $event->getCommittee()) {
            return false;
        }

        return $adherent->isHostOf($committee);
    }
}
