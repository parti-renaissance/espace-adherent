<?php

namespace AppBundle\Mailjet\Transport;

use AppBundle\Mailjet\EmailTemplate;

interface TransportInterface
{
    /**
     * Delivers the email to the recipients.
     *
     * @param EmailTemplate $email
     */
    public function sendTemplateEmail(EmailTemplate $email): void;
}
