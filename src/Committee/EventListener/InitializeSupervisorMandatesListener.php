<?php

namespace App\Committee\EventListener;

use App\Committee\CommitteeAdherentMandateManager;
use App\Committee\Event\ApproveCommitteeEvent;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class InitializeSupervisorMandatesListener implements EventSubscriberInterface
{
    public function __construct(private readonly CommitteeAdherentMandateManager $mandateManager)
    {
    }

    public static function getSubscribedEvents(): array
    {
        return [ApproveCommitteeEvent::class => 'onCommitteeApproved'];
    }

    public function onCommitteeApproved(ApproveCommitteeEvent $event): void
    {
        $committee = $event->getCommittee();

        foreach ($committee->getProvisionalSupervisors() as $provisionalSupervisor) {
            $this->mandateManager->updateSupervisorMandate($provisionalSupervisor->getAdherent(), $committee, true);
        }
    }
}
