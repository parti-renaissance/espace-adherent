<?php

namespace App\Event;

use App\AppCodeEnum;
use App\Events;
use App\Mailer\MailerService;
use App\Mailer\Message\BesoinDEurope\BesoinDEuropeEventRegistrationConfirmationMessage;
use App\Mailer\Message\Renaissance\RenaissanceEventRegistrationConfirmationMessage;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

class EventRegistrationSubscriber implements EventSubscriberInterface
{
    private $mailer;
    private $urlGenerator;

    public function __construct(
        MailerService $transactionalMailer,
        UrlGeneratorInterface $urlGenerator
    ) {
        $this->mailer = $transactionalMailer;
        $this->urlGenerator = $urlGenerator;
    }

    public static function getSubscribedEvents(): array
    {
        return [Events::EVENT_REGISTRATION_CREATED => 'sendRegistrationEmail'];
    }

    public function sendRegistrationEmail(EventRegistrationEvent $event): void
    {
        if (!$event->getSendMail()) {
            return;
        }

        $registration = $event->getRegistration();

        if (AppCodeEnum::isJeMengage($registration->getSource())) {
            $message = BesoinDEuropeEventRegistrationConfirmationMessage::createFromRegistration(
                $registration,
                $this->generateUrl('vox_app').'/event-detail?id='.$registration->getEvent()->getUuidAsString(),
            );
        } else {
            $message = RenaissanceEventRegistrationConfirmationMessage::createFromRegistration(
                $registration,
                $this->generateUrl('app_renaissance_event_show', ['slug' => $event->getSlug()])
            );
        }

        $this->mailer->sendMessage($message);
    }

    private function generateUrl(string $route, array $params = []): string
    {
        return $this->urlGenerator->generate($route, $params, UrlGeneratorInterface::ABSOLUTE_URL);
    }
}
