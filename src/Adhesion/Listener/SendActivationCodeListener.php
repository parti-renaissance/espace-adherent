<?php

namespace App\Adhesion\Listener;

use App\Adhesion\Command\GenerateActivationCodeCommand;
use App\Membership\AdherentEvents;
use App\Membership\Event\AdherentEvent;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\Messenger\MessageBusInterface;

class SendActivationCodeListener implements EventSubscriberInterface
{
    public function __construct(private readonly MessageBusInterface $bus)
    {
    }

    public static function getSubscribedEvents(): array
    {
        return [AdherentEvents::REGISTRATION_COMPLETED => 'onRegistrationCompleted'];
    }

    public function onRegistrationCompleted(AdherentEvent $event): void
    {
        $adherent = $event->getAdherent();

        if (!$adherent->isV2() || $adherent->getActivatedAt()) {
            return;
        }

        // If the adherent is eligible for membership payment, we don't send the activation code on creation, but after the payment
        if ($adherent->isEligibleForMembershipPayment()) {
            return;
        }

        $this->bus->dispatch(new GenerateActivationCodeCommand($adherent, true));
    }
}
