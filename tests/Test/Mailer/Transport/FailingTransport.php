<?php

namespace Tests\AppBundle\Test\Mailer\Transport;

use AppBundle\Mailer\EmailTemplate;
use AppBundle\Mailer\Exception\MailerException;
use AppBundle\Mailer\Transport\TransportInterface;

class FailingTransport implements TransportInterface
{
    public function sendTemplateEmail(EmailTemplate $email): void
    {
        throw new MailerException('Unable to send email to recipients.');
    }
}
