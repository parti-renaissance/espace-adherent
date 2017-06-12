<?php

namespace Tests\AppBundle\Test\Mailjet\Transport;

use AppBundle\Mailjet\EmailTemplate;
use AppBundle\Mailjet\Transport\TransportInterface;
use Psr\Log\LoggerInterface;

class NullTransport implements TransportInterface
{
    private $logger;

    public function __construct(LoggerInterface $logger = null)
    {
        $this->logger = $logger;
    }

    public function sendTemplateEmail(EmailTemplate $email): void
    {
        if ($this->logger) {
            $this->logger->info('[mailjet] sending email with Mailjet.', [
                'message' => $email->getBody(),
            ]);
        }

        $email->delivered('Delivered using NULL transport');
    }
}
