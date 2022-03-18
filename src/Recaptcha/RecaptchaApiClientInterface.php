<?php

namespace App\Recaptcha;

interface RecaptchaApiClientInterface
{
    public function verify(string $token, ?string $siteKey): bool;
}
