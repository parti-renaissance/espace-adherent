<?php

declare(strict_types=1);

namespace Tests\App\Analytics\PostHog;

use App\Analytics\PostHog\IngestProxyController;
use PHPUnit\Framework\TestCase;
use Psr\Log\LoggerInterface;
use Symfony\Component\HttpClient\Exception\TransportException;
use Symfony\Component\HttpClient\MockHttpClient;
use Symfony\Component\HttpClient\Response\MockResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\RateLimiter\LimiterInterface;
use Symfony\Component\RateLimiter\RateLimit;
use Symfony\Component\RateLimiter\RateLimiterFactoryInterface;

class IngestProxyControllerTest extends TestCase
{
    private function makeFactoryAlwaysAllow(): RateLimiterFactoryInterface
    {
        $factory = $this->createMock(RateLimiterFactoryInterface::class);
        $limiter = $this->createMock(LimiterInterface::class);
        $rateLimit = $this->createStub(RateLimit::class);
        $rateLimit->method('isAccepted')->willReturn(true);
        $limiter->method('consume')->willReturn($rateLimit);
        $factory->method('create')->willReturn($limiter);

        return $factory;
    }

    private function makeFactoryAlwaysDeny(): RateLimiterFactoryInterface
    {
        $factory = $this->createMock(RateLimiterFactoryInterface::class);
        $limiter = $this->createMock(LimiterInterface::class);
        $rateLimit = $this->createStub(RateLimit::class);
        $rateLimit->method('isAccepted')->willReturn(false);
        $limiter->method('consume')->willReturn($rateLimit);
        $factory->method('create')->willReturn($limiter);

        return $factory;
    }

    public function testForwardEventEndpoint(): void
    {
        $mockClient = new MockHttpClient([
            new MockResponse('{"status":"ok"}', [
                'http_code' => 200,
                'response_headers' => ['content-type' => 'application/json'],
            ]),
        ]);
        $logger = $this->createMock(LoggerInterface::class);
        $controller = new IngestProxyController(
            $mockClient,
            'https://eu.i.posthog.com',
            $logger,
            $this->makeFactoryAlwaysAllow(),
        );
        $request = Request::create('/ingest/e/', 'POST', server: ['CONTENT_TYPE' => 'application/json'], content: '{"event":"test"}');

        $response = $controller('e/', $request);

        $this->assertSame(200, $response->getStatusCode());
        $this->assertStringContainsString('"status":"ok"', $response->getContent());
    }

    public function testTimeoutReturns504(): void
    {
        $mockClient = new MockHttpClient(function () {
            throw new TransportException('timeout');
        });
        $logger = $this->createMock(LoggerInterface::class);
        $logger->expects($this->once())->method('warning')->with($this->stringContains('PostHog proxy'));
        $controller = new IngestProxyController(
            $mockClient,
            'https://eu.i.posthog.com',
            $logger,
            $this->makeFactoryAlwaysAllow(),
        );

        $response = $controller('e/', Request::create('/ingest/e/', 'POST'));

        $this->assertSame(504, $response->getStatusCode());
    }

    public function testSanitizesCookieHeader(): void
    {
        $mockClient = new MockHttpClient([
            new MockResponse('', [
                'http_code' => 200,
                'response_headers' => [
                    'set-cookie' => 'ph_session=abc; Path=/',
                    'content-type' => 'application/json',
                ],
            ]),
        ]);
        $logger = $this->createMock(LoggerInterface::class);
        $controller = new IngestProxyController(
            $mockClient,
            'https://eu.i.posthog.com',
            $logger,
            $this->makeFactoryAlwaysAllow(),
        );

        $response = $controller('decide/', Request::create('/ingest/decide/', 'POST'));

        $this->assertEmpty($response->headers->getCookies());
        $this->assertNull($response->headers->get('set-cookie'));
    }

    public function testPreservesContentEncodingHeader(): void
    {
        $mockClient = new MockHttpClient([
            new MockResponse('', [
                'http_code' => 200,
                'response_headers' => [
                    'content-encoding' => 'gzip',
                    'content-type' => 'application/json',
                ],
            ]),
        ]);
        $logger = $this->createMock(LoggerInterface::class);
        $controller = new IngestProxyController(
            $mockClient,
            'https://eu.i.posthog.com',
            $logger,
            $this->makeFactoryAlwaysAllow(),
        );

        $response = $controller('e/', Request::create('/ingest/e/', 'POST'));

        $this->assertSame('gzip', $response->headers->get('content-encoding'));
    }

    public function testRateLimitBlocksExcess(): void
    {
        $mockClient = new MockHttpClient(function () {
            $this->fail('HTTP client should not be called when rate limit denied');
        });
        $logger = $this->createMock(LoggerInterface::class);
        $controller = new IngestProxyController(
            $mockClient,
            'https://eu.i.posthog.com',
            $logger,
            $this->makeFactoryAlwaysDeny(),
        );

        $response = $controller('e/', Request::create('/ingest/e/', 'POST'));

        $this->assertSame(429, $response->getStatusCode());
        $this->assertSame('60', $response->headers->get('Retry-After'));
    }
}
