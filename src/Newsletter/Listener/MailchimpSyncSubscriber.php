<?php

namespace AppBundle\Newsletter\Listener;

use AppBundle\Mailchimp\Synchronisation\Command\AddNewsletterMemberCommand;
use AppBundle\Mailchimp\Synchronisation\Command\RemoveNewsletterMemberCommand;
use AppBundle\Newsletter\Events;
use AppBundle\Newsletter\NewsletterEvent;
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
            Events::SUBSCRIBE => 'onSubscribe',
            Events::UNSUBSCRIBE => 'onUnsubscribe',
        ];
    }

    public function onSubscribe(NewsletterEvent $event): void
    {
        $this->bus->dispatch(new AddNewsletterMemberCommand($event->getNewsletter()->getId()));
    }

    public function onUnsubscribe(NewsletterEvent $event): void
    {
        $this->bus->dispatch(new RemoveNewsletterMemberCommand($event->getNewsletter()->getEmail()));
    }
}
