<?php

namespace Tests\AppBundle\Test\Recaptcha;

use AppBundle\Recaptcha\RecaptchaApiClient;

class DummyRecaptchaApiClient extends RecaptchaApiClient
{
    public function __construct()
    {
        parent::__construct('dummy-test-key');
    }

    public function verify(string $answer, string $clientIp = null): bool
    {
        return true;
    }
}
