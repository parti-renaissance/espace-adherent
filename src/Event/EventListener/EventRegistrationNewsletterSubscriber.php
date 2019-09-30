<?php

namespace AppBundle\Event\EventListener;

use AppBundle\Entity\NewsletterSubscription;
use AppBundle\Event\EventRegistrationEvent;
use AppBundle\Events;
use AppBundle\Newsletter\NewsletterSubscriptionHandler;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class EventRegistrationNewsletterSubscriber implements EventSubscriberInterface
{
    private $handler;

    public function __construct(NewsletterSubscriptionHandler $handler)
    {
        $this->handler = $handler;
    }

    public static function getSubscribedEvents()
    {
        return [Events::EVENT_REGISTRATION_CREATED => 'createNewsletter'];
    }

    public function createNewsletter(EventRegistrationEvent $event): void
    {
        $registration = $event->getRegistration();

        if (!$registration->isNewsletterSubscriber()) {
            return;
        }

        $this->handler->subscribe(new NewsletterSubscription(
            $registration->getEmailAddress(),
            null,
            null,
            true
        ));
    }
}
