<?php

namespace AppBundle\Mailjet\Transport;

use AppBundle\Mailjet\MailjetTemplateEmail;

interface MailjetMessageTransportInterface
{
    /**
     * Delivers the email to the recipients.
     *
     * @param MailjetTemplateEmail $email
     */
    public function sendTemplateEmail(MailjetTemplateEmail $email);
}
