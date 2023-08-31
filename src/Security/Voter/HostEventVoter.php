<?php

namespace App\Security\Voter;

use App\Entity\Adherent;
use App\Entity\Event\BaseEvent;
use App\Entity\Event\CommitteeEvent;
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

    protected function supports(string $attribute, $event): bool
    {
        return EventPermissions::HOST === $attribute && $event instanceof BaseEvent;
    }

    /**
     * @param CommitteeEvent $event
     */
    protected function doVoteOnAttribute(string $attribute, Adherent $adherent, $event): bool
    {
        if ($delegatedAccessUuid = $this->session->get(DelegatedAccess::ATTRIBUTE_KEY)) {
            $adherent = $adherent->getReceivedDelegatedAccessByUuid($delegatedAccessUuid)->getDelegator();
        }

        if ($event->getOrganizer() && $adherent->equals($event->getOrganizer())) {
            return true;
        }

        if ($event instanceof CommitteeEvent) {
            if (!$committee = $event->getCommittee()) {
                return false;
            }

            return $adherent->isSupervisorOf($committee) || $adherent->isHostOf($committee);
        }

        return false;
    }
}
