<?php

namespace App\Committee\EventListener;

use App\Committee\CommitteeAdherentMandateManager;
use App\Committee\Event\CommitteeEvent;
use App\Events;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class InitializeSupervisorMandatesListener implements EventSubscriberInterface
{
    public function __construct(private readonly CommitteeAdherentMandateManager $mandateManager)
    {
    }

    public static function getSubscribedEvents(): array
    {
        return [
            Events::COMMITTEE_APPROVED => 'onCommitteeApproved',
        ];
    }

    public function onCommitteeApproved(CommitteeEvent $event): void
    {
        $committee = $event->getCommittee();

        foreach ($committee->getProvisionalSupervisors() as $provisionalSupervisor) {
            $this->mandateManager->updateSupervisorMandate($provisionalSupervisor->getAdherent(), $committee, true);
        }
    }
}
