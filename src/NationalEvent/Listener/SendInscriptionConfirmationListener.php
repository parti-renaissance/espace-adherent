<?php

namespace App\NationalEvent\Listener;

use App\Mailer\MailerService;
use App\Mailer\Message\BesoinDEurope\NationalEventInscriptionConfirmationMessage;
use App\NationalEvent\NewNationalEventInscriptionEvent;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class SendInscriptionConfirmationListener implements EventSubscriberInterface
{
    public function __construct(private readonly MailerService $transactionalMailer)
    {
    }

    public static function getSubscribedEvents(): array
    {
        return [NewNationalEventInscriptionEvent::class => 'sendConfirmationEmail'];
    }

    public function sendConfirmationEmail(NewNationalEventInscriptionEvent $event): void
    {
        $this->transactionalMailer->sendMessage(NationalEventInscriptionConfirmationMessage::create($event->eventInscription));
    }
}
