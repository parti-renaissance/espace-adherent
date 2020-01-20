<?php

namespace AppBundle\Mailer\Transport;

use AppBundle\Mailer\AbstractEmailTemplate;

interface TransportInterface
{
    /**
     * Delivers the email to the recipients.
     */
    public function sendTemplateEmail(AbstractEmailTemplate $email): void;
}
