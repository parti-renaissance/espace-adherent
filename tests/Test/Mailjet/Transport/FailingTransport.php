<?php

namespace Tests\AppBundle\Test\Mailjet\Transport;

use AppBundle\Mailjet\Exception\MailjetException;
use AppBundle\Mailjet\EmailTemplate;
use AppBundle\Mailjet\Transport\TransportInterface;

class FailingTransport implements TransportInterface
{
    public function sendTemplateEmail(EmailTemplate $email): void
    {
        throw new MailjetException('Unable to send email to recipients.');
    }
}
