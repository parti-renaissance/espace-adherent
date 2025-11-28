<?php

declare(strict_types=1);

namespace App\Legislative\Newsletter\Listener;

use App\Legislative\Newsletter\Events;
use App\Legislative\Newsletter\LegislativeNewsletterEvent;
use App\Newsletter\Command\MailchimpSyncLegislativeNewsletterCommand;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\Messenger\MessageBusInterface;

class MailchimpSyncNewsletterSubscriber implements EventSubscriberInterface
{
    private MessageBusInterface $bus;

    public function __construct(MessageBusInterface $bus)
    {
        $this->bus = $bus;
    }

    public static function getSubscribedEvents(): array
    {
        return [Events::NEWSLETTER_CONFIRMATION => 'onNewsletterConfirmation'];
    }

    public function onNewsletterConfirmation(LegislativeNewsletterEvent $event): void
    {
        $this->bus->dispatch(new MailchimpSyncLegislativeNewsletterCommand($event->getNewsletter()->getId()));
    }
}
