<?php

namespace AppBundle\Mailer;

use AppBundle\Mailer\Message\Message;
use AppBundle\Mailer\Message\MessageRegistry;
use AppBundle\Mailjet\EmailTemplate as MailjetTemplate;

class EmailTemplateFactory
{
    private $messageRegistry;
    private $defaultSenderEmail;
    private $defaultSenderName;

    public function __construct(MessageRegistry $messageRegistry, string $defaultSenderEmail, string $defaultSenderName)
    {
        $this->messageRegistry = $messageRegistry;
        $this->defaultSenderEmail = $defaultSenderEmail;
        $this->defaultSenderName = $defaultSenderName;
    }

    public function createFromMessage(Message $message): EmailTemplate
    {
        if ($message->isV2()) {
            return MailjetTemplate::createWithMessage(
                $message,
                $this->messageRegistry->getMessageTemplate($message),
                $this->defaultSenderEmail,
                $this->defaultSenderName
            );
        }

        return MailjetTemplate::createWithMessage(
            $message,
            $message->getTemplate(),
            $this->defaultSenderEmail,
            $this->defaultSenderName
        );
    }
}
