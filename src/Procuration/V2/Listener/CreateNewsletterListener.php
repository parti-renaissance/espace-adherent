<?php

namespace App\Procuration\V2\Listener;

use App\Newsletter\NewsletterTypeEnum;
use App\Procuration\V2\Event\NewProcurationEvent;
use App\Renaissance\Newsletter\NewsletterManager;
use App\Renaissance\Newsletter\SubscriptionRequest;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class CreateNewsletterListener implements EventSubscriberInterface
{
    public function __construct(private readonly NewsletterManager $newsletterManager)
    {
    }

    public static function getSubscribedEvents(): array
    {
        return [NewProcurationEvent::class => ['createNewsletter', -255]];
    }

    public function createNewsletter(NewProcurationEvent $event): void
    {
        $procuration = $event->procuration;

        if (!$procuration->joinNewsletter) {
            return;
        }

        $newsletterRequest = new SubscriptionRequest();

        $newsletterRequest->postalCode = $procuration->getPostalCode();
        $newsletterRequest->email = $procuration->email;

        $newsletterRequest->cguAccepted = true;
        $newsletterRequest->source = NewsletterTypeEnum::SITE_PROCURATION;

        $this->newsletterManager->saveSubscription($newsletterRequest);
    }
}
