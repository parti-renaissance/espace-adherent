<?php

namespace App\Event;

use App\Events;
use App\Mailer\MailerService;
use App\Mailer\Message\EventRegistrationConfirmationMessage;
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

        $this->mailer->sendMessage(EventRegistrationConfirmationMessage::createFromRegistration(
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
