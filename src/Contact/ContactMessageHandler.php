<?php

declare(strict_types=1);

namespace App\Contact;

use App\Mailer\MailerService;
use App\Mailer\Message\AdherentContactMessage;
use App\Mailer\Message\Renaissance\RenaissanceAdherentContactMessage;

class ContactMessageHandler
{
    private $mailer;

    public function __construct(MailerService $transactionalMailer)
    {
        $this->mailer = $transactionalMailer;
    }

    public function handle(ContactMessage $contactMessage, bool $fromRenaissance = false): void
    {
        $fromRenaissance
            ? $this->mailer->sendMessage(RenaissanceAdherentContactMessage::createFromModel($contactMessage))
            : $this->mailer->sendMessage(AdherentContactMessage::createFromModel($contactMessage));
    }
}
