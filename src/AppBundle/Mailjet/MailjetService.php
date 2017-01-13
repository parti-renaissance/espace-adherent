<?php

namespace AppBundle\Mailjet;

use AppBundle\Mailjet\Exception\MailjetException;
use AppBundle\Mailjet\Message\MailjetMessage;

class MailjetService
{
    private $transport;
    private $senderEmail;
    private $senderName;

    public function __construct(MailjetMessageTransportInterface $transport, string $senderEmail, string $senderName)
    {
        $this->transport = $transport;
        $this->senderEmail = $senderEmail;
        $this->senderName = $senderName;
    }

    public function sendMessage(MailjetMessage $message): bool
    {
        $delivered = true;
        try {
            $email = MailjetTemplateEmail::createWithMailjetMessage($message, $this->senderEmail, $this->senderName);
            $this->transport->sendTemplateEmail($email);
        } catch (MailjetException $exception) {
            $delivered = false;
        }

        return $delivered;
    }
}
