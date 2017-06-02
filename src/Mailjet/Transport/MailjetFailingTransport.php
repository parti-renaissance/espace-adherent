<?php

namespace AppBundle\Mailjet\Transport;

use AppBundle\Mailjet\Exception\MailjetException;
use AppBundle\Mailjet\MailjetTemplateEmail;

class MailjetFailingTransport implements MailjetMessageTransportInterface
{
    public function sendTemplateEmail(MailjetTemplateEmail $email)
    {
        throw new MailjetException('Unable to send email to recipients.');
    }
}
