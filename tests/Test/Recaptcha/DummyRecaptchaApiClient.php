<?php

namespace Tests\App\Test\Recaptcha;

use App\Recaptcha\RecaptchaApiClientInterface;

class DummyRecaptchaApiClient implements RecaptchaApiClientInterface
{
    public function verify(string $token, ?string $siteKey): bool
    {
        return 'wrong_answer' !== $token;
    }
}
