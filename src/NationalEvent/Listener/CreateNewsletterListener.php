<?php

namespace App\NationalEvent\Listener;

use App\NationalEvent\NewNationalEventInscriptionEvent;
use App\Newsletter\NewsletterTypeEnum;
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
        return [NewNationalEventInscriptionEvent::class => ['createNewsletter', -255]];
    }

    public function createNewsletter(NewNationalEventInscriptionEvent $event): void
    {
        $eventInscription = $event->eventInscription;

        if (!$eventInscription->joinNewsletter) {
            return;
        }

        $newsletterRequest = new SubscriptionRequest();

        $newsletterRequest->postalCode = $eventInscription->postalCode;
        $newsletterRequest->email = $eventInscription->addressEmail;

        $newsletterRequest->cguAccepted = true;
        $newsletterRequest->source = NewsletterTypeEnum::SITE_EU;

        $this->newsletterManager->saveSubscription($newsletterRequest);
    }
}
