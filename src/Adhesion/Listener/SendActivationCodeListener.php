<?php

namespace App\Adhesion\Listener;

use App\Adhesion\AdhesionStepEnum;
use App\Adhesion\Command\GenerateActivationCodeCommand;
use App\Adhesion\Events\NewCotisationEvent;
use App\Membership\Event\UserEvent;
use App\Membership\UserEvents;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\Messenger\MessageBusInterface;

class SendActivationCodeListener implements EventSubscriberInterface
{
    public function __construct(private readonly MessageBusInterface $bus)
    {
    }

    public static function getSubscribedEvents(): array
    {
        return [
            UserEvents::USER_CREATED => 'sendActivationCode',
            NewCotisationEvent::class => 'sendActivationCode',
        ];
    }

    public function sendActivationCode(UserEvent $event): void
    {
        $adherent = $event->getAdherent();

        if (!$adherent->isPending() && $adherent->hasFinishedAdhesionStep(AdhesionStepEnum::ACTIVATION)) {
            return;
        }

        // If the adherent is eligible for membership payment, we don't send the activation code on creation, but after the payment
        if (!$event instanceof NewCotisationEvent && $adherent->isEligibleForMembershipPayment()) {
            return;
        }

        $this->bus->dispatch(new GenerateActivationCodeCommand($adherent, true));
    }
}
