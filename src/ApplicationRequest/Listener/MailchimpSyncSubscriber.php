<?php

namespace AppBundle\ApplicationRequest\Listener;

use AppBundle\ApplicationRequest\ApplicationRequestEvent;
use AppBundle\ApplicationRequest\Events;
use AppBundle\Mailchimp\Synchronisation\Command\AddApplicationRequestCandidateCommand;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\Messenger\MessageBusInterface;

class MailchimpSyncSubscriber implements EventSubscriberInterface
{
    private $bus;

    public function __construct(MessageBusInterface $bus)
    {
        $this->bus = $bus;
    }

    public static function getSubscribedEvents()
    {
        return [
            Events::CREATED => ['syncCandidateWithMailchimp', -1],
        ];
    }

    public function syncCandidateWithMailchimp(ApplicationRequestEvent $event): void
    {
        $this->bus->dispatch(new AddApplicationRequestCandidateCommand(
            $event->getApplicationRequest()->getId(),
            \get_class($event->getApplicationRequest())
        ));
    }
}
