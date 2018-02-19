<?php

namespace AppBundle\Mailer;

use AppBundle\Mailer\Message\Message;
use AppBundle\Mailer\Message\MessageRegistry;
use AppBundle\Mailjet\EmailTemplate as MailjetTemplate;

class EmailTemplateFactory
{
    private $messageRegistry;
    private $senderEmail;
    private $senderName;

    public function __construct(MessageRegistry $messageRegistry, string $senderEmail, string $senderName)
    {
        $this->messageRegistry = $messageRegistry;
        $this->senderEmail = $senderEmail;
        $this->senderName = $senderName;
    }

    public function createFromMessage(Message $message): EmailTemplate
    {
        return MailjetTemplate::createWithMessage(
            $message,
            $this->messageRegistry->getMessageTemplate($message),
            $this->senderEmail,
            $this->senderName
        );
    }
}
