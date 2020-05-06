<?php

namespace App\Mailer\Transport;

use App\Mailer\AbstractEmailTemplate;

interface TransportInterface
{
    /**
     * Delivers the email to the recipients.
     */
    public function sendTemplateEmail(AbstractEmailTemplate $email): void;
}
