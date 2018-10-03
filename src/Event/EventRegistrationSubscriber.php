<?php

namespace AppBundle\Event;

use AppBundle\Events;
use AppBundle\Mail\Transactional\EventRegistrationConfirmationMail;
use EnMarche\MailerBundle\MailPost\MailPostInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

class EventRegistrationSubscriber implements EventSubscriberInterface
{
    private $mailPost;
    private $urlGenerator;

    public function __construct(MailPostInterface $mailPost, UrlGeneratorInterface $urlGenerator)
    {
        $this->mailPost = $mailPost;
        $this->urlGenerator = $urlGenerator;
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

        $eventUrl = $this->generateUrl('app_event_show', [
            'slug' => $event->getSlug(),
        ]);

        $this->mailPost->address(
            EventRegistrationConfirmationMail::class,
            EventRegistrationConfirmationMail::createRecipientFor($registration),
            null,
            EventRegistrationConfirmationMail::createTemplateVarsFrom($registration->getEvent(), $eventUrl),
            EventRegistrationConfirmationMail::SUBJECT
        );
    }

    private function generateUrl(string $route, array $params = []): string
    {
        return $this->urlGenerator->generate($route, $params, UrlGeneratorInterface::ABSOLUTE_URL);
    }
}
