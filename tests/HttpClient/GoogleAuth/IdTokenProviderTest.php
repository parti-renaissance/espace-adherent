<?php

declare(strict_types=1);

namespace Tests\App\HttpClient\GoogleAuth;

use App\HttpClient\GoogleAuth\IdTokenException;
use App\HttpClient\GoogleAuth\IdTokenProvider;
use Google\Auth\FetchAuthTokenCache;
use Google\Auth\FetchAuthTokenInterface;
use PHPUnit\Framework\TestCase;
use Psr\Log\NullLogger;
use Symfony\Component\Cache\Adapter\ArrayAdapter;

final class IdTokenProviderTest extends TestCase
{
    public function testTokenReusedWhileCached(): void
    {
        $pool = new ArrayAdapter();
        $fakes = [];
        $provider = new IdTokenProvider($pool, new NullLogger(), self::cachingFactory($pool, $fakes));

        self::assertSame('tok:audA', $provider->getIdToken('audA'));
        self::assertSame('tok:audA', $provider->getIdToken('audA'));

        self::assertSame(1, $fakes['audA']->callCount, 'A cached id_token must be reused, not refetched.');
    }

    public function testTokenRegeneratedAfterCacheCleared(): void
    {
        $pool = new ArrayAdapter();
        $fakes = [];
        $provider = new IdTokenProvider($pool, new NullLogger(), self::cachingFactory($pool, $fakes));

        $provider->getIdToken('audA');
        $pool->clear();
        $provider->getIdToken('audA');

        self::assertSame(2, $fakes['audA']->callCount, 'After cache eviction the token must be refetched.');
    }

    public function testNoCrossAudienceLeak(): void
    {
        $pool = new ArrayAdapter();
        $fakes = [];
        $provider = new IdTokenProvider($pool, new NullLogger(), self::cachingFactory($pool, $fakes));

        self::assertSame('tok:audA', $provider->getIdToken('audA'));
        self::assertSame('tok:audB', $provider->getIdToken('audB'));
    }

    public function testMissingIdTokenThrows(): void
    {
        $pool = new ArrayAdapter();
        $provider = new IdTokenProvider($pool, new NullLogger(), static function (string $audience) use ($pool): FetchAuthTokenInterface {
            return new FetchAuthTokenCache(new FakeIdTokenFetcher($audience, []), self::cacheConfig($audience), $pool);
        });

        $this->expectException(IdTokenException::class);

        $provider->getIdToken('audA');
    }

    public function testFetcherThrowSurfacesAsIdTokenException(): void
    {
        $boom = new \RuntimeException('metadata server unreachable');
        $provider = new IdTokenProvider(new ArrayAdapter(), new NullLogger(), static function () use ($boom): FetchAuthTokenInterface {
            return new ThrowingFetcher($boom);
        });

        try {
            $provider->getIdToken('audA');
            self::fail('Expected an IdTokenException.');
        } catch (IdTokenException $exception) {
            self::assertSame($boom, $exception->getPrevious(), 'The original failure must be chained.');
        }
    }

    /**
     * Wraps a per-audience fake in a REAL FetchAuthTokenCache + ArrayAdapter (exercises the actual
     * google/auth caching), recording each fake by audience so a test can assert how many times the
     * underlying fetcher was really hit.
     *
     * @param array<string, FakeIdTokenFetcher> $fakes
     *
     * @return callable(string): FetchAuthTokenInterface
     */
    private static function cachingFactory(ArrayAdapter $pool, array &$fakes): callable
    {
        return static function (string $audience) use ($pool, &$fakes): FetchAuthTokenInterface {
            $fake = new FakeIdTokenFetcher($audience, ['id_token' => 'tok:'.$audience]);
            $fakes[$audience] = $fake;

            return new FetchAuthTokenCache($fake, self::cacheConfig($audience), $pool);
        };
    }

    /**
     * @return array{lifetime: int, prefix: string}
     */
    private static function cacheConfig(string $audience): array
    {
        return ['lifetime' => 3300, 'prefix' => 'idtok_'.sha1($audience).'_'];
    }
}

/**
 * Test fetcher: counts real fetches and returns a fixed token payload for its audience. A concrete
 * class (not a PHPUnit mock) so it can be wrapped in a real FetchAuthTokenCache.
 */
final class FakeIdTokenFetcher implements FetchAuthTokenInterface
{
    public int $callCount = 0;

    /**
     * @param array<string, mixed> $token
     */
    public function __construct(private readonly string $audience, private readonly array $token)
    {
    }

    public function fetchAuthToken(?callable $httpHandler = null): array
    {
        ++$this->callCount;

        return $this->token;
    }

    public function getCacheKey(): string
    {
        return $this->audience;
    }

    public function getLastReceivedToken(): ?array
    {
        return null;
    }
}

final class ThrowingFetcher implements FetchAuthTokenInterface
{
    public function __construct(private readonly \Throwable $error)
    {
    }

    public function fetchAuthToken(?callable $httpHandler = null): array
    {
        throw $this->error;
    }

    public function getCacheKey(): string
    {
        return 'throwing';
    }

    public function getLastReceivedToken(): ?array
    {
        return null;
    }
}
