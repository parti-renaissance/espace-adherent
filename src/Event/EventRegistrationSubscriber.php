<?php

namespace App\Event;

use App\Coalition\CoalitionUrlGenerator;
use App\Entity\Event\CoalitionEvent;
use App\Events;
use App\Mailer\MailerService;
use App\Mailer\Message\Coalition\CoalitionsEventRegistrationConfirmationMessage;
use App\Mailer\Message\EventRegistrationConfirmationMessage;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

class EventRegistrationSubscriber implements EventSubscriberInterface
{
    private $mailer;
    private $urlGenerator;
    private $coalitionUrlGenerator;

    public function __construct(
        MailerService $transactionalMailer,
        UrlGeneratorInterface $urlGenerator,
        CoalitionUrlGenerator $coalitionUrlGenerator
    ) {
        $this->mailer = $transactionalMailer;
        $this->urlGenerator = $urlGenerator;
        $this->coalitionUrlGenerator = $coalitionUrlGenerator;
    }

    public static function getSubscribedEvents()
    {
        return [Events::EVENT_REGISTRATION_CREATED => 'sendRegistrationEmail'];
    }

    public function sendRegistrationEmail(EventRegistrationEvent $event)
    {
        if (!$event->getSendMail()) {
            return;
        }

        $registration = $event->getRegistration();
        $registrationEvent = $registration->getEvent();
        if ($registrationEvent->isCoalitionsEvent()) {
            $message = CoalitionsEventRegistrationConfirmationMessage::create(
                $registration,
                $registrationEvent instanceof CoalitionEvent
                    ? $this->coalitionUrlGenerator->generateCoalitionEventLink($registrationEvent)
                    : $this->coalitionUrlGenerator->generateCauseEventLink($registrationEvent)
            );
        } else {
            $message = EventRegistrationConfirmationMessage::createFromRegistration(
                $registration,
                $this->generateUrl('app_committee_event_show', [
                    'slug' => $event->getSlug(),
                ])
            );
        }

        $this->mailer->sendMessage($message);
    }

    private function generateUrl(string $route, array $params = []): string
    {
        return $this->urlGenerator->generate($route, $params, UrlGeneratorInterface::ABSOLUTE_URL);
    }
}
