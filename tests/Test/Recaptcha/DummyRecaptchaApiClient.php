<?php

namespace Tests\App\Test\Recaptcha;

use App\Recaptcha\RecaptchaApiClientInterface;

class DummyRecaptchaApiClient implements RecaptchaApiClientInterface
{
    public function verify(string $answer, string $clientIp = null): bool
    {
        return true;
    }
}
