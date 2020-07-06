<?php

namespace App\Mailchimp\Synchronisation\EventListener;

use App\ElectedRepresentative\ElectedRepresentativeEvent;
use App\ElectedRepresentative\ElectedRepresentativeEvents;
use App\Mailchimp\Synchronisation\Command\ElectedRepresentativeChangeCommand;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\Messenger\MessageBusInterface;

class ElectedRepresentativeEventSubscriber implements EventSubscriberInterface
{
    private $bus;

    public function __construct(MessageBusInterface $bus)
    {
        $this->bus = $bus;
    }

    public static function getSubscribedEvents()
    {
        return [
            ElectedRepresentativeEvents::POST_UPDATE => 'postUpdate',
        ];
    }

    public function postUpdate(ElectedRepresentativeEvent $event): void
    {
        $electedRepresentative = $event->getElectedRepresentative();

        $this->bus->dispatch(new ElectedRepresentativeChangeCommand(
            $electedRepresentative->getUuid(),
            $electedRepresentative->getEmailAddress()
        ));
    }
}
