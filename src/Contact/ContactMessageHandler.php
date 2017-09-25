<?php

namespace AppBundle\Contact;

use AppBundle\Mailer\MailerService;
use AppBundle\Mailer\Message\AdherentContactMessage;

class ContactMessageHandler
{
    private $mailer;

    public function __construct(MailerService $mailer)
    {
        $this->mailer = $mailer;
    }

    public function handle(ContactMessage $contactMessage)
    {
        $this->mailer->sendMessage(AdherentContactMessage::createFromMdel($contactMessage));
    }
}
