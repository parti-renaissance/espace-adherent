<?php

namespace AppBundle\ApplicationRequest\Listener;

use AppBundle\ApplicationRequest\ApplicationRequestEvent;
use AppBundle\ApplicationRequest\Events;
use AppBundle\Mailer\MailerService;
use AppBundle\Mailer\Message\ApplicationRequestConfirmationMessage;
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
