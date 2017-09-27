<?php

namespace AppBundle\Mailjet;

use AppBundle\Mailjet\Message\MailjetMessage;

class EmailTemplateFactory
{
    private $senderEmail;
    private $senderName;

    public function __construct(string $senderEmail, string $senderName)
    {
        $this->senderEmail = $senderEmail;
        $this->senderName = $senderName;
    }

    public function createFromMailjetMessage(MailjetMessage $message): EmailTemplate
    {
        return EmailTemplate::createWithMailjetMessage($message, $this->senderEmail, $this->senderName);
    }
}
