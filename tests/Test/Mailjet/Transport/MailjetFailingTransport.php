<?php

namespace Tests\AppBundle\Test\Mailjet\Transport;

use AppBundle\Mailjet\Exception\MailjetException;
use AppBundle\Mailjet\MailjetTemplateEmail;
use AppBundle\Mailjet\Transport\MailjetMessageTransportInterface;

class MailjetFailingTransport implements MailjetMessageTransportInterface
{
    public function sendTemplateEmail(MailjetTemplateEmail $email)
    {
        throw new MailjetException('Unable to send email to recipients.');
    }
}
