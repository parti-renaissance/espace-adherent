<?php

declare(strict_types=1);

namespace Tests\App\HttpClient\GoogleAuth;

use App\HttpClient\GoogleAuth\IdTokenAuthMisconfiguredException;
use App\HttpClient\GoogleAuth\IdTokenException;
use App\HttpClient\GoogleAuth\IdTokenHttpClient;
use App\HttpClient\GoogleAuth\IdTokenProviderInterface;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use Psr\Log\NullLogger;
use Symfony\Component\HttpClient\MockHttpClient;
use Symfony\Component\HttpClient\Response\MockResponse;

final class IdTokenHttpClientTest extends TestCase
{
    private const BASE_URI = 'https://indexer.example.run.app';

    public function testRelativeUrlAttachesBearerForBaseUriAudience(): void
    {
        $captured = [];
        $inner = new MockHttpClient(static function (string $method, string $url, array $options) use (&$captured): MockResponse {
            $captured = $options;

            return new MockResponse('ok');
        }, self::BASE_URI);

        $provider = $this->createMock(IdTokenProviderInterface::class);
        $provider
            ->expects(self::once())
            ->method('getIdToken')
            ->with(self::BASE_URI)
            ->willReturn('tok:'.self::BASE_URI);

        $client = $this->decorator($inner, $provider);
        $client->request('POST', '/index', ['json' => ['x' => 1]]);

        self::assertSame(
            ['Authorization: Bearer tok:'.self::BASE_URI],
            $captured['normalized_headers']['authorization'] ?? null,
        );
    }

    public function testAbsoluteUrlOnConfiguredHostIsAllowed(): void
    {
        $inner = new MockHttpClient(static fn (): MockResponse => new MockResponse('ok'), self::BASE_URI);
        $provider = $this->createMock(IdTokenProviderInterface::class);
        $provider->expects(self::once())->method('getIdToken')->with(self::BASE_URI)->willReturn('tok');

        $this->decorator($inner, $provider)->request('POST', self::BASE_URI.'/index');

        self::assertSame(1, $inner->getRequestsCount());
    }

    public function testCrossHostAbsoluteUrlFailsClosed(): void
    {
        $inner = new MockHttpClient();
        $provider = $this->createMock(IdTokenProviderInterface::class);
        $provider->expects(self::never())->method('getIdToken');

        $client = $this->decorator($inner, $provider);

        $this->assertFailsClosed(static fn () => $client->request('POST', 'https://evil.example/index'));
        self::assertSame(0, $inner->getRequestsCount());
    }

    public function testBaseUriOverrideFailsClosed(): void
    {
        $inner = new MockHttpClient();
        $provider = $this->createMock(IdTokenProviderInterface::class);
        $provider->expects(self::never())->method('getIdToken');

        $client = $this->decorator($inner, $provider);

        $this->assertFailsClosed(static fn () => $client->request('POST', '/x', ['base_uri' => 'https://evil.example']));
        self::assertSame(0, $inner->getRequestsCount());
    }

    /**
     * @param array<string, mixed> $options
     */
    #[DataProvider('provideConcurrentCredentials')]
    public function testConcurrentCredentialFailsClosed(array $options): void
    {
        $inner = new MockHttpClient();
        $provider = $this->createMock(IdTokenProviderInterface::class);
        $provider->expects(self::never())->method('getIdToken');

        $client = $this->decorator($inner, $provider);

        $this->assertFailsClosed(static fn () => $client->request('POST', '/index', $options));
        self::assertSame(0, $inner->getRequestsCount());
    }

    /**
     * @return iterable<string, array{array<string, mixed>}>
     */
    public static function provideConcurrentCredentials(): iterable
    {
        yield 'Authorization header (assoc)' => [['headers' => ['Authorization' => 'Bearer x']]];
        yield 'Authorization header (list)' => [['headers' => ['Authorization: Bearer x']]];
        yield 'auth_bearer option' => [['auth_bearer' => 'x']];
        yield 'auth_basic option' => [['auth_basic' => 'user:pass']];
    }

    public function testTokenFailurePropagates(): void
    {
        $inner = new MockHttpClient();
        $provider = $this->createMock(IdTokenProviderInterface::class);
        $provider
            ->expects(self::once())
            ->method('getIdToken')
            ->with(self::BASE_URI)
            ->willThrowException(new IdTokenException('boom'));

        $client = $this->decorator($inner, $provider);

        try {
            $client->request('POST', '/index');
            self::fail('Expected an IdTokenException.');
        } catch (IdTokenException) {
            // expected
        }

        self::assertSame(0, $inner->getRequestsCount());
    }

    public function testWithOptionsKeepsDecorator(): void
    {
        // withOptions() never reaches the provider → a stub (no expectations), per the strict mock rule.
        $client = $this->decorator(new MockHttpClient(), $this->createStub(IdTokenProviderInterface::class));

        self::assertInstanceOf(IdTokenHttpClient::class, $client->withOptions(['timeout' => 5]));
    }

    public function testWithOptionsRejectsBaseUri(): void
    {
        $client = $this->decorator(new MockHttpClient(), $this->createStub(IdTokenProviderInterface::class));

        $this->assertFailsClosed(static fn () => $client->withOptions(['base_uri' => 'https://evil.example']));
    }

    private function decorator(
        MockHttpClient $inner,
        IdTokenProviderInterface $provider,
        string $baseUri = self::BASE_URI,
    ): IdTokenHttpClient {
        return new IdTokenHttpClient($inner, $provider, $baseUri, new NullLogger());
    }

    private function assertFailsClosed(callable $call): void
    {
        try {
            $call();
            self::fail('Expected an IdTokenAuthMisconfiguredException.');
        } catch (IdTokenAuthMisconfiguredException $exception) {
            self::assertInstanceOf(IdTokenAuthMisconfiguredException::class, $exception);
        }
    }
}
