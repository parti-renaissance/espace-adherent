<?php

namespace AppBundle\Event\EventListener;

use AppBundle\Entity\NewsletterSubscription;
use AppBundle\Event\EventRegistrationEvent;
use AppBundle\Events;
use AppBundle\Newsletter\NewsletterSubscriptionHandler;
use AppBundle\Repository\AdherentRepository;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class EventRegistrationNewsletterSubscriber implements EventSubscriberInterface
{
    private $handler;
    private $adherentRepository;
    private $validator;

    public function __construct(
        NewsletterSubscriptionHandler $handler,
        AdherentRepository $adherentRepository,
        ValidatorInterface $validator
    ) {
        $this->handler = $handler;
        $this->adherentRepository = $adherentRepository;
        $this->validator = $validator;
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

        if ($this->adherentRepository->isAdherent($registration->getEmailAddress())) {
            return;
        }

        $newsletter = new NewsletterSubscription($registration->getEmailAddress(), null, null, true);

        if ($this->validator->validate($newsletter)->count()) {
            return;
        }

        $this->handler->subscribe($newsletter);
    }
}
