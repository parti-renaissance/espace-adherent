<?php

namespace AppBundle\Mailjet;

interface ClientInterface
{
    public function sendEmail(string $email): string;
}
