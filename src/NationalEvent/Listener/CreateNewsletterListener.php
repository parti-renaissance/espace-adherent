<?php

declare(strict_types=1);

namespace App\NationalEvent\Listener;

use App\NationalEvent\Event\NationalEventInscriptionEventInterface;
use App\NationalEvent\Event\NewNationalEventInscriptionEvent;
use App\NationalEvent\Event\UpdateNationalEventInscriptionEvent;
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
        return [
            NewNationalEventInscriptionEvent::class => ['createNewsletter', -255],
            UpdateNationalEventInscriptionEvent::class => ['createNewsletter', -255],
        ];
    }

    public function createNewsletter(NationalEventInscriptionEventInterface $event): void
    {
        $eventInscription = $event->getEventInscription();

        if (!$eventInscription->joinNewsletter || !$eventInscription->needSendNewsletterConfirmation) {
            return;
        }

        $newsletterRequest = new SubscriptionRequest();

        $newsletterRequest->postalCode = $eventInscription->postalCode;
        $newsletterRequest->email = $eventInscription->addressEmail;

        $newsletterRequest->cguAccepted = true;
        $newsletterRequest->source = NewsletterTypeEnum::FROM_MEETING;

        $this->newsletterManager->saveSubscription($newsletterRequest);
    }
}
