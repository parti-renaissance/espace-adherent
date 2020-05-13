<?php

namespace App\Event\EventListener;

use App\Entity\NewsletterSubscription;
use App\Event\EventRegistrationEvent;
use App\Events;
use App\Newsletter\NewsletterSubscriptionHandler;
use App\Repository\AdherentRepository;
use App\Repository\NewsletterSubscriptionRepository;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class EventRegistrationNewsletterSubscriber implements EventSubscriberInterface
{
    private $handler;
    private $adherentRepository;
    private $newsletterSubscriptionRepository;
    private $validator;

    public function __construct(
        NewsletterSubscriptionHandler $handler,
        AdherentRepository $adherentRepository,
        NewsletterSubscriptionRepository $newsletterSubscriptionRepository,
        ValidatorInterface $validator
    ) {
        $this->handler = $handler;
        $this->adherentRepository = $adherentRepository;
        $this->newsletterSubscriptionRepository = $newsletterSubscriptionRepository;
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

        $newsletter = $this->newsletterSubscriptionRepository->findOneNotConfirmedByEmail($registration->getEmailAddress());
        if ($newsletter) {
            $newsletter->setFromEvent(true);
        } else {
            $newsletter = new NewsletterSubscription($registration->getEmailAddress(), null, null, true);
        }

        if ($this->validator->validate($newsletter)->count()) {
            return;
        }

        $this->handler->subscribe($newsletter);
    }
}
