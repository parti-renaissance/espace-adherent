<?php

namespace App\Security\Voter\Event;

use App\Entity\Adherent;
use App\Entity\Event\BaseEvent;
use App\Entity\Event\CommitteeEvent;
use App\Entity\MyTeam\DelegatedAccess;
use App\Security\Voter\AbstractAdherentVoter;
use Symfony\Component\HttpFoundation\RequestStack;

class HostEventVoter extends AbstractAdherentVoter
{
    public function __construct(private readonly RequestStack $requestStack)
    {
    }

    protected function supports(string $attribute, $subject): bool
    {
        return 'HOST_EVENT' === $attribute && $subject instanceof BaseEvent;
    }

    /**
     * @param CommitteeEvent $event
     */
    protected function doVoteOnAttribute(string $attribute, Adherent $adherent, $event): bool
    {
        if ($delegatedAccessUuid = $this->requestStack->getSession()->get(DelegatedAccess::ATTRIBUTE_KEY)) {
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
