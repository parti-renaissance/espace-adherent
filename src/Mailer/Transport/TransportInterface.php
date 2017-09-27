<?php

namespace AppBundle\Mailer\Transport;

use AppBundle\Mailer\EmailTemplate;

interface TransportInterface
{
    /**
     * Delivers the email to the recipients.
     *
     * @param EmailTemplate $email
     */
    public function sendTemplateEmail(EmailTemplate $email): void;
}
