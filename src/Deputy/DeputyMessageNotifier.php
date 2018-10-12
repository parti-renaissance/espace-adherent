<?php

namespace AppBundle\Deputy;

use AppBundle\Mail\Campaign\DeputyMail;
use EnMarche\MailerBundle\MailPost\MailPostInterface;

class DeputyMessageNotifier
{
    private $mailPost;

    public function __construct(MailPostInterface $mailPost)
    {
        $this->mailPost = $mailPost;
    }

    public function sendMessage(DeputyMessage $message): void
    {
        $message->getRecipients();

        $this->mailPost->address(
            DeputyMail::class,
            DeputyMail::createRecipients($message->getRecipients()),
            DeputyMail::createRecipientFromAdherent($message->getFrom()),
            DeputyMail::createTemplateVars($message),
            $message->getSubject(),
            DeputyMail::createSender($message->getFrom())
        );
    }
}
