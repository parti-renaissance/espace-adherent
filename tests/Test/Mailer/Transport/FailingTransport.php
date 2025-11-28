<?php

declare(strict_types=1);

namespace Tests\App\Test\Mailer\Transport;

use App\Mailer\AbstractEmailTemplate;
use App\Mailer\Exception\MailerException;
use App\Mailer\Transport\TransportInterface;

class FailingTransport implements TransportInterface
{
    public function sendTemplateEmail(AbstractEmailTemplate $email, bool $async = true): void
    {
        throw new MailerException('Unable to send email to recipients.');
    }
}
