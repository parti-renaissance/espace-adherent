<?php

declare(strict_types=1);

namespace Tests\App\Test\Recaptcha;

use App\Recaptcha\RecaptchaApiClientInterface;

class DummyRecaptchaApiClient implements RecaptchaApiClientInterface
{
    public function supports(string $name): bool
    {
        return true;
    }

    public function verify(string $token, ?string $siteKey): bool
    {
        return 'wrong_answer' !== $token;
    }
}
