<?php

namespace App\NationalEvent\Listener;

use App\Mailer\MailerService;
use App\Mailer\Message\Renaissance\NationalEventInscriptionConfirmationMessage;
use App\NationalEvent\Event\NewNationalEventInscriptionEvent;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

class SendInscriptionConfirmationListener implements EventSubscriberInterface
{
    public function __construct(
        private readonly MailerService $transactionalMailer,
        private readonly UrlGeneratorInterface $urlGenerator,
        private readonly string $secret,
    ) {
    }

    public static function getSubscribedEvents(): array
    {
        return [NewNationalEventInscriptionEvent::class => 'sendConfirmationEmail'];
    }

    public function sendConfirmationEmail(NewNationalEventInscriptionEvent $event): void
    {
        $this->transactionalMailer->sendMessage(NationalEventInscriptionConfirmationMessage::create(
            $event->eventInscription,
            $this->urlGenerator->generate('app_national_event_edit_inscription', ['uuid' => $uuid = $event->eventInscription->getUuid()->toString(), 'token' => hash_hmac('sha256', $uuid, $this->secret)], UrlGeneratorInterface::ABSOLUTE_URL)
        ));
    }
}
