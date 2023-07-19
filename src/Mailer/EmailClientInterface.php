<?php

namespace App\Mailer;

interface EmailClientInterface
{
    public function sendEmail(string $email, bool $resend = false): string;

    public function renderEmail(EmailTemplateInterface $email): string;
}
