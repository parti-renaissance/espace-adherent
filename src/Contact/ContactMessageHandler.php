<?php

namespace AppBundle\Contact;

use AppBundle\Mail\Campaign\AdherentContactMail;
use EnMarche\MailerBundle\MailPost\MailPostInterface;

class ContactMessageHandler
{
    private $mailPost;

    public function __construct(MailPostInterface $mailPost)
    {
        $this->mailPost = $mailPost;
    }

    public function handle(ContactMessage $contactMessage): void
    {
        $this->mailPost->address(
            AdherentContactMail::class,
            AdherentContactMail::createRecipientFor($contactMessage),
            AdherentContactMail::createRecipientFromAdherent($contactMessage->getFrom())
        );
    }
}
