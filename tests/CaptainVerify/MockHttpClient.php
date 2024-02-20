<?php

namespace Tests\App\CaptainVerify;

use Symfony\Component\HttpClient\MockHttpClient as SymfonyMockHttpClient;
use Symfony\Component\HttpClient\Response\MockResponse;

class MockHttpClient extends SymfonyMockHttpClient
{
    public function __construct()
    {
        parent::__construct([new MockResponse(<<<JSON
            {
                "credits":870,
                "result":"valid",
                "details":"",
                "free":true,
                "role":false,
                "disposable":false,
                "ok_for_all":false,
                "protected":false,
                "did_you_mean":"john.doe@gmail.com",
                "email":"john.doe@gmail.com",
                "email_normalized":"john.doe@gmail.com",
                "success":true,
                "message":null
                }
            JSON
        )]);
    }
}
