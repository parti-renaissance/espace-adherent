<?php

namespace App\Recaptcha;

interface RecaptchaApiClientInterface
{
    public function verify(string $answer, string $clientIp = null): bool;
}
