<?php

namespace Tests\AppBundle\Test\Mailer\Transport;

use AppBundle\Mailer\EmailTemplate;
use AppBundle\Mailer\Transport\TransportInterface;
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
            $this->logger->info('[mailer] sending email.', [
                'message' => $email->getBody(),
            ]);
        }

        $email->delivered('Delivered using NULL transport');
    }
}
