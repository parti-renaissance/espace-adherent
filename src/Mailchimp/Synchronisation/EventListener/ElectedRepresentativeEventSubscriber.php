<?php

namespace App\Mailchimp\Synchronisation\EventListener;

use App\ElectedRepresentative\ElectedRepresentativeEvent;
use App\ElectedRepresentative\ElectedRepresentativeEvents;
use App\Mailchimp\Synchronisation\Command\ElectedRepresentativeChangeCommand;
use App\Mailchimp\Synchronisation\Command\ElectedRepresentativeDeleteCommand;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\Messenger\MessageBusInterface;

class ElectedRepresentativeEventSubscriber implements EventSubscriberInterface
{
    private $bus;
    private $oldEmail;

    public function __construct(MessageBusInterface $bus)
    {
        $this->bus = $bus;
    }

    public static function getSubscribedEvents()
    {
        return [
            ElectedRepresentativeEvents::BEFORE_UPDATE => 'onBeforeUpdate',
            ElectedRepresentativeEvents::POST_UPDATE => 'postUpdate',
        ];
    }

    public function onBeforeUpdate(ElectedRepresentativeEvent $event): void
    {
        $this->oldEmail = $event->getElectedRepresentative()->getEmailAddress();
    }

    public function postUpdate(ElectedRepresentativeEvent $event): void
    {
        $electedRepresentative = $event->getElectedRepresentative();

        if (!$electedRepresentative->getEmailAddress() && $this->oldEmail) {
            $this->bus->dispatch(new ElectedRepresentativeDeleteCommand($this->oldEmail));

            return;
        }

        $this->bus->dispatch(new ElectedRepresentativeChangeCommand(
            $electedRepresentative->getUuid(),
            $this->oldEmail ?? $electedRepresentative->getEmailAddress()
        ));
    }
}
