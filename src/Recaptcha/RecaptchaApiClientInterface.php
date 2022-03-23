<?php

namespace App\Recaptcha;

interface RecaptchaApiClientInterface
{
    public function supports(string $name): bool;

    public function verify(string $token, ?string $siteKey): bool;
}
