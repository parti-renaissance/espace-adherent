<?php

namespace App\Mailer;

interface EmailClientInterface
{
    public function sendEmail(string $email): string;

    public function renderEmail(EmailTemplateInterface $email): string;
}
