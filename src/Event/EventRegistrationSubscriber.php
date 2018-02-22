<?php

namespace AppBundle\Event;

use AppBundle\Events;
use AppBundle\Mailer\MailerService;
use AppBundle\Mailer\Message\EventRegistrationConfirmationMessage;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

class EventRegistrationSubscriber implements EventSubscriberInterface
{
    private $mailer;
    private $urlGenerator;

    public function __construct(MailerService $mailer, UrlGeneratorInterface $urlGenerator)
    {
        $this->mailer = $mailer;
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

        $this->mailer->sendMessage(EventRegistrationConfirmationMessage::create(
            $event->getRegistration(),
            $this->generateUrl('app_event_show', [
                'slug' => $event->getSlug(),
            ])
        ));
    }

    private function generateUrl(string $route, array $params = []): string
    {
        return $this->urlGenerator->generate($route, $params, UrlGeneratorInterface::ABSOLUTE_URL);
    }
}
