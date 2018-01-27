<?php

namespace AppBundle\Mailer;

use AppBundle\Mailer\Message\Message;
use AppBundle\Mailjet\EmailTemplate as MailjetTemplate;

class EmailTemplateFactory
{
    private $senderEmail;
    private $senderName;

    public function __construct(string $senderEmail, string $senderName)
    {
        $this->senderEmail = $senderEmail;
        $this->senderName = $senderName;
    }

    public function createFromMessage(Message $message): EmailTemplate
    {
        return MailjetTemplate::createWithMessage(
            $message,
            $this->senderEmail,
            $this->senderName
        );
    }
}
