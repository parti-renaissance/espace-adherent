<?php

declare(strict_types=1);

namespace App\Procuration\Listener;

use App\Newsletter\NewsletterTypeEnum;
use App\Procuration\Event\ProcurationEvent;
use App\Procuration\Event\ProcurationEvents;
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
        return [
            ProcurationEvents::PROXY_CREATED => ['createNewsletter', -255],
            ProcurationEvents::REQUEST_CREATED => ['createNewsletter', -255],
        ];
    }

    public function createNewsletter(ProcurationEvent $event): void
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
