<?php

namespace AppBundle\Contact;

use AppBundle\Mailjet\MailjetService;
use AppBundle\Mailjet\Message\AdherentContactMessage;

class ContactMessageHandler
{
    private $mailjet;

    public function __construct(MailjetService $mailjet)
    {
        $this->mailjet = $mailjet;
    }

    public function handle(ContactMessage $contactMessage)
    {
        $this->mailjet->sendMessage(AdherentContactMessage::createFromMdel($contactMessage));
    }
}
