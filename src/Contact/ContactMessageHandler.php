<?php

namespace App\Contact;

use App\Mailer\MailerService;
use App\Mailer\Message\AdherentContactMessage;

class ContactMessageHandler
{
    private $mailer;

    public function __construct(MailerService $mailer)
    {
        $this->mailer = $mailer;
    }

    public function handle(ContactMessage $contactMessage)
    {
        $this->mailer->sendMessage(AdherentContactMessage::createFromModel($contactMessage));
    }
}
