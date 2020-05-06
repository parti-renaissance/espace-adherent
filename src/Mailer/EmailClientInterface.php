<?php

namespace App\Mailer;

interface EmailClientInterface
{
    public function sendEmail(string $email): string;
}
