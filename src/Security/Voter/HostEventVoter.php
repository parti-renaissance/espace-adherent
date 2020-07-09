<?php

namespace App\Security\Voter;

use App\Entity\Adherent;
use App\Entity\Event;
use App\Entity\MyTeam\DelegatedAccess;
use App\Event\EventPermissions;
use Symfony\Component\HttpFoundation\Session\SessionInterface;

class HostEventVoter extends AbstractAdherentVoter
{
    /** @var SessionInterface */
    private $session;

    public function __construct(SessionInterface $session)
    {
        $this->session = $session;
    }

    protected function supports($attribute, $event)
    {
        return EventPermissions::HOST === $attribute && $event instanceof Event;
    }

    /**
     * @param Event $event
     */
    protected function doVoteOnAttribute(string $attribute, Adherent $adherent, $event): bool
    {
        if ($delegatedAccessUuid = $this->session->get(DelegatedAccess::ATTRIBUTE_KEY)) {
            $adherent = $adherent->getReceivedDelegatedAccessByUuid($delegatedAccessUuid)->getDelegator();
        }

        if ($event->getOrganizer() && $adherent->equals($event->getOrganizer())) {
            return true;
        }

        if (!$committee = $event->getCommittee()) {
            return false;
        }

        return $adherent->isHostOf($committee);
    }
}
