<?php

namespace AppBundle\Mailjet;

use Psr\Log\LoggerInterface;

class MailjetNullTransport implements MailjetMessageTransportInterface
{
    private $logger;

    public function __construct(LoggerInterface $logger = null)
    {
        $this->logger = $logger;
    }

    public function sendTemplateEmail(MailjetTemplateEmail $email)
    {
        if ($this->logger) {
            $this->logger->info('[mailjet] sending email with Mailjet.', [
                'message' => $email->getBody(),
            ]);
        }
    }
}
