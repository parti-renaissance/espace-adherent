<?php

declare(strict_types=1);

namespace App\Recaptcha;

interface RecaptchaApiClientInterface
{
    public function supports(string $name): bool;

    public function verify(string $token, ?string $siteKey): bool;
}
