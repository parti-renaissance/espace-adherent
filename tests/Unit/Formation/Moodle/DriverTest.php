<?php

declare(strict_types=1);

namespace Tests\App\Unit\Formation\Moodle;

use App\Formation\Moodle\Driver;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpClient\MockHttpClient;
use Symfony\Component\HttpClient\Response\MockResponse;

class DriverTest extends TestCase
{
    /**
     * Moodle answers HTTP 200 on failure, carrying the error in the body. The write must read
     * the response and raise, otherwise the caller (e.g. the repair command) reports a false success.
     */
    public function testDeleteUserRaisesWhenMoodleReturnsAnException(): void
    {
        $http = new MockHttpClient(new MockResponse(
            '{"exception":"webservice_access_exception","errorcode":"accessexception","message":"Access control exception"}'
        ), 'https://moodle.test');

        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessageMatches('/accessexception/');

        new Driver($http)->deleteUser(2913);
    }

    public function testUpdateUserRaisesWhenMoodleReturnsAnException(): void
    {
        $http = new MockHttpClient(new MockResponse(
            '{"exception":"invalid_parameter_exception","errorcode":"invalidparameter","message":"Email address already exists"}'
        ), 'https://moodle.test');

        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessageMatches('/invalidparameter/');

        new Driver($http)->updateUser(22, ['email' => 'a@example.com', 'username' => 'a@example.com']);
    }

    /**
     * On success a void Moodle function returns the literal "null": the request must still be issued
     * (the response consumed) and no exception raised.
     */
    public function testDeleteUserConsumesTheResponseAndSucceedsOnNullBody(): void
    {
        $requests = [];
        $http = new MockHttpClient(function (string $method, string $url, array $options) use (&$requests): MockResponse {
            $requests[] = [$method, $url];

            return new MockResponse('null');
        }, 'https://moodle.test');

        new Driver($http)->deleteUser(2913);

        self::assertCount(1, $requests);
        [$method, $url] = $requests[0];
        self::assertSame('POST', $method);
        self::assertStringContainsString('core_user_delete_users', $url);
    }
}
