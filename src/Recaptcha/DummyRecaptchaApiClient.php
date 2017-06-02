<?php

namespace AppBundle\Recaptcha;

/**
 * This class is only meant for internal and functional test purpose.
 */
final class DummyRecaptchaApiClient extends RecaptchaApiClient
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
