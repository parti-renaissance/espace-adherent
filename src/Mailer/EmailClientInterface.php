<?php

namespace App\Mailer;

interface EmailClientInterface
{
    public function sendEmail(string $email, bool $resend = false, bool $useTemplateEndpoint = true): string;
}
