<?php

namespace AppBundle\Mailjet;

interface MailjetMessageTransportInterface
{
    /**
     * Delivers the email to the recipients.
     *
     * @param MailjetTemplateEmail $email
     */
    public function sendTemplateEmail(MailjetTemplateEmail $email);
}
