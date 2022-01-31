<?php

namespace App\Committee\EventListener;

use App\Committee\CommitteeAdherentMandateManager;
use App\Committee\CommitteeEvent;
use App\Events;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class InitializeSupervisorMandatesListener implements EventSubscriberInterface
{
    private $mandateManager;

    public function __construct(CommitteeAdherentMandateManager $mandateManager)
    {
        $this->mandateManager = $mandateManager;
    }

    public static function getSubscribedEvents()
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
