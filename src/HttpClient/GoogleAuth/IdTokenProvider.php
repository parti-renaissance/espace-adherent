<?php

declare(strict_types=1);

namespace App\HttpClient\GoogleAuth;

use Google\Auth\ApplicationDefaultCredentials;
use Google\Auth\FetchAuthTokenInterface;
use Psr\Cache\CacheItemPoolInterface;
use Psr\Log\LoggerInterface;

class IdTokenProvider implements IdTokenProviderInterface
{
    /**
     * Google id_tokens issued by the GKE metadata server live 3600s; we evict 300s early so a request
     * never carries a token that is within 5 minutes of expiry.
     */
    private const CACHE_LIFETIME_SECONDS = 3300;

    /**
     * @var callable(string): FetchAuthTokenInterface
     */
    private $fetcherFactory;

    /**
     * @var array<string, FetchAuthTokenInterface>
     */
    private array $fetchers = [];

    /**
     * @param (callable(string): FetchAuthTokenInterface)|null $fetcherFactory test seam; defaults to ADC
     */
    public function __construct(
        private readonly CacheItemPoolInterface $cache,
        private readonly LoggerInterface $logger,
        ?callable $fetcherFactory = null,
    ) {
        $this->fetcherFactory = $fetcherFactory ?? function (string $audience): FetchAuthTokenInterface {
            // Passing the pool makes getIdTokenCredentials wrap the fetcher in a FetchAuthTokenCache;
            // the per-audience prefix guarantees an audience-specific cache key whatever the credential
            // type (some credential types omit the audience from their own getCacheKey()).
            return ApplicationDefaultCredentials::getIdTokenCredentials(
                $audience,
                null,
                ['lifetime' => self::CACHE_LIFETIME_SECONDS, 'prefix' => 'idtok_'.sha1($audience).'_'],
                $this->cache,
            );
        };
    }

    public function getIdToken(string $audience): string
    {
        $fetcher = $this->fetchers[$audience] ??= ($this->fetcherFactory)($audience);

        try {
            $token = $fetcher->fetchAuthToken();
        } catch (\Throwable $exception) {
            // Log the audience and message only — never the token.
            $this->logger->error('Failed to fetch a Google ID token.', ['audience' => $audience, 'message' => $exception->getMessage()]);

            throw new IdTokenException(\sprintf('Unable to fetch a Google ID token for audience "%s".', $audience), previous: $exception);
        }

        $idToken = \is_array($token) ? ($token['id_token'] ?? null) : null;
        if (!\is_string($idToken) || '' === $idToken) {
            $this->logger->error('Google ID token response carried no id_token.', ['audience' => $audience]);

            throw new IdTokenException(\sprintf('No id_token returned for audience "%s".', $audience));
        }

        return $idToken;
    }
}
