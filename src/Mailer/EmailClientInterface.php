<?php

namespace AppBundle\Mailer;

interface EmailClientInterface
{
    public function sendEmail(string $email): string;
}
