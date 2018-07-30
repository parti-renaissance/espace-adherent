<?php

namespace AppBundle\Contact;

use AppBundle\Mailer\Message\AdherentContactMessage;
use EnMarche\MailerBundle\MailPost\MailPostInterface;

class ContactMessageHandler
{
    private $mailPost;

    public function __construct(MailPostInterface $mailPost)
    {
        $this->mailPost = $mailPost;
    }

    public function handle(ContactMessage $contactMessage)
    {
        $this->mailPost->address(
            AdherentContactMessage::class,
            AdherentContactMessage::createRecipientFor($contactMessage),
            AdherentContactMessage::createRecipientFromAdherent($contactMessage->getFrom())
        );
    }
}
