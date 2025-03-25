<?php

namespace App\Adherent\Referral\Listener;

use App\Adherent\Referral\Command\LinkReferrerWithNewAdherentCommand;
use App\Adhesion\Events\NewCotisationEvent;
use App\Membership\Event\UserEvent;
use App\Membership\UserEvents;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\Messenger\MessageBusInterface;

class AdhesionSubscriber implements EventSubscriberInterface
{
    public function __construct(private readonly MessageBusInterface $bus)
    {
    }

    public function updateReferrals(UserEvent $event): void
    {
        $this->bus->dispatch(new LinkReferrerWithNewAdherentCommand(
            $event->getAdherent()->getUuid(),
            $event instanceof NewCotisationEvent,
            $event->referrerPublicId,
            $event->referralIdentifier
        ));
    }

    public static function getSubscribedEvents(): array
    {
        return [
            UserEvents::USER_CREATED => ['updateReferrals', -257],
            NewCotisationEvent::class => ['updateReferrals', -257],
        ];
    }
}
