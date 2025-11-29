<?php

declare(strict_types=1);

namespace App\Event\EventListener;

use App\Event\EventRegistrationEvent;
use App\Events;
use App\Newsletter\NewsletterTypeEnum;
use App\Renaissance\Newsletter\NewsletterManager;
use App\Renaissance\Newsletter\SubscriptionRequest;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class EventRegistrationNewsletterSubscriber implements EventSubscriberInterface
{
    public function __construct(private readonly NewsletterManager $newsletterManager)
    {
    }

    public static function getSubscribedEvents(): array
    {
        return [Events::EVENT_REGISTRATION_CREATED => 'createNewsletter'];
    }

    public function createNewsletter(EventRegistrationEvent $event): void
    {
        $registration = $event->getRegistration();

        if (!$registration->isNewsletterSubscriber()) {
            return;
        }

        $newsletterRequest = new SubscriptionRequest();

        $newsletterRequest->postalCode = $registration->getPostalCode();
        $newsletterRequest->email = $registration->getEmailAddress();

        $newsletterRequest->cguAccepted = true;
        $newsletterRequest->source = NewsletterTypeEnum::FROM_EVENT;

        $this->newsletterManager->saveSubscription($newsletterRequest);
    }
}
