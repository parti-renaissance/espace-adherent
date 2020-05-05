<?php

namespace AppBundle\Newsletter\Listener;

use AppBundle\Mailchimp\Synchronisation\Command\RemoveNewsletterMemberCommand;
use AppBundle\Newsletter\Command\MailchimpSyncNewsletterSubscriptionEntityCommand;
use AppBundle\Newsletter\Events;
use AppBundle\Newsletter\NewsletterEvent;
use Psr\Log\LoggerInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\Messenger\MessageBusInterface;

class MailchimpSyncSubscriber implements EventSubscriberInterface
{
    private $bus;
    private $logger;

    public function __construct(MessageBusInterface $bus, LoggerInterface $logger)
    {
        $this->bus = $bus;
        $this->logger = $logger;
    }

    public static function getSubscribedEvents()
    {
        return [
            Events::CONFIRMATION => ['onSubscribe', -1],
            Events::UPDATE => ['onSubscribe', -1],
            Events::UNSUBSCRIBE => ['onUnsubscribe', -1],
        ];
    }

    public function onSubscribe(NewsletterEvent $event): void
    {
        if (!$event->getNewsletter()->isConfirmed()) {
            $this->logger->error(\sprintf(
                'NewsletterSubscription with id "%d" is not confirmed to proceed a subscription.',
                $event->getNewsletter()->getId()
            ));

            return;
        }

        $this->bus->dispatch(new MailchimpSyncNewsletterSubscriptionEntityCommand(
            $event->getNewsletter()->getId()
        ));
    }

    public function onUnsubscribe(NewsletterEvent $event): void
    {
        $this->bus->dispatch(new RemoveNewsletterMemberCommand($event->getNewsletter()->getEmail()));
    }
}
