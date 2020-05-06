<?php

namespace App\ApplicationRequest\Listener;

use App\ApplicationRequest\ApplicationRequestEvent;
use App\ApplicationRequest\Events;
use App\Mailer\MailerService;
use App\Mailer\Message\ApplicationRequestConfirmationMessage;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

class SendConfirmationRequestSubscriber implements EventSubscriberInterface
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
        return [
            Events::CREATED => 'sendConfirmationMail',
        ];
    }

    public function sendConfirmationMail(ApplicationRequestEvent $event): void
    {
        $this->mailer->sendMessage(ApplicationRequestConfirmationMessage::create(
            $event->getApplicationRequest(),
            $this->urlGenerator->generate('app_application_request_request', [], UrlGeneratorInterface::ABSOLUTE_URL)
        ));
    }
}
