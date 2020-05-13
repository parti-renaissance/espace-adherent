<?php

namespace Tests\App\Test\Recaptcha;

use App\Recaptcha\RecaptchaApiClient;
use GuzzleHttp\Exception\ConnectException;
use GuzzleHttp\Psr7\Request;

class DummyRecaptchaApiClient extends RecaptchaApiClient
{
    public function __construct()
    {
        parent::__construct('dummy-test-key');
    }

    public function verify(string $answer, string $clientIp = null): bool
    {
        if ('connection_failure' === $answer) {
            throw new ConnectException('', new Request('GET', '/inscription'));
        }

        return true;
    }
}
