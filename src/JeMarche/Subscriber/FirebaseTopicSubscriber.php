<?php

namespace App\JeMarche\Subscriber;

use App\Adherent\Command\UpdateFirebaseTopicsCommand;
use App\Entity\PostAddress;
use App\Membership\Event\UserEvent;
use App\Membership\UserEvents;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\Messenger\MessageBusInterface;

class FirebaseTopicSubscriber implements EventSubscriberInterface
{
    private ?PostAddress $addressBefore = null;

    public function __construct(private readonly MessageBusInterface $bus)
    {
    }

    public static function getSubscribedEvents(): array
    {
        return [
            UserEvents::USER_BEFORE_UPDATE => 'onBeforeUpdate',
            UserEvents::USER_UPDATED => 'onAfterUpdate',
        ];
    }

    public function onBeforeUpdate(UserEvent $event): void
    {
        $this->addressBefore = clone $event->getAdherent()->getPostAddress();
    }

    public function onAfterUpdate(UserEvent $event): void
    {
        $adherent = $event->getAdherent();

        if ($this->addressBefore && !$adherent->getPostAddress()->equals($this->addressBefore)) {
            $this->bus->dispatch(new UpdateFirebaseTopicsCommand($adherent->getUuid()));
        }
    }
}
