<?php

namespace Tests\App\Test\Mailer;

use App\Mailer\EmailClientInterface;
use Psr\Log\LoggerInterface;

class NullEmailClient implements EmailClientInterface
{
    private $logger;

    public function __construct(?LoggerInterface $logger = null)
    {
        $this->logger = $logger;
    }

    public function sendEmail(string $email, bool $resend = false, bool $useTemplateEndpoint = true): string
    {
        if ($this->logger) {
            $this->logger->info('[mailer] sending email with Mailer.', ['email' => $email]);
        }

        return 'Delivered using NULL client';
    }
}
