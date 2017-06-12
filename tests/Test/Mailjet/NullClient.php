<?php

namespace Tests\AppBundle\Test\Mailjet;

use AppBundle\Mailjet\ClientInterface;
use Psr\Log\LoggerInterface;

class NullClient implements ClientInterface
{
    private $logger;

    public function __construct(LoggerInterface $logger = null)
    {
        $this->logger = $logger;
    }

    public function sendEmail(string $email): string
    {
        if ($this->logger) {
            $this->logger->info('[mailjet] sending email with Mailjet.', ['email' => $email]);
        }

        return 'Delivered using NULL client';
    }
}
