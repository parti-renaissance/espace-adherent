<?php

declare(strict_types=1);

namespace App\HttpClient\GoogleAuth;

use Psr\Log\LoggerInterface;
use Symfony\Contracts\HttpClient\HttpClientInterface;
use Symfony\Contracts\HttpClient\ResponseInterface;
use Symfony\Contracts\HttpClient\ResponseStreamInterface;

class IdTokenHttpClient implements HttpClientInterface
{
    public function __construct(
        private readonly HttpClientInterface $client,
        private readonly IdTokenProviderInterface $tokenProvider,
        private readonly string $baseUri,
        private readonly LoggerInterface $logger,
    ) {
    }

    public function request(string $method, string $url, array $options = []): ResponseInterface
    {
        if (isset($options['base_uri'])) {
            $this->failClosed('A base_uri override is not allowed on an ID-token-authenticated request.');
        }

        $urlHost = parse_url($url, \PHP_URL_HOST);
        if (\is_string($urlHost) && $urlHost !== parse_url($this->baseUri, \PHP_URL_HOST)) {
            $this->failClosed(\sprintf('An ID-token-authenticated request must not target another host: "%s".', $url));
        }

        if (isset($options['auth_bearer']) || isset($options['auth_basic']) || $this->hasAuthorizationHeader($options)) {
            $this->failClosed('A conflicting credential is already set on an ID-token-authenticated request.');
        }

        $options['auth_bearer'] = $this->tokenProvider->getIdToken($this->baseUri);

        return $this->client->request($method, $url, $options);
    }

    public function stream(ResponseInterface|iterable $responses, ?float $timeout = null): ResponseStreamInterface
    {
        return $this->client->stream($responses, $timeout);
    }

    public function withOptions(array $options): static
    {
        if (isset($options['base_uri'])) {
            $this->failClosed('A base_uri override is not allowed on an ID-token-authenticated client.');
        }

        return new self($this->client->withOptions($options), $this->tokenProvider, $this->baseUri, $this->logger);
    }

    /**
     * @param array<string, mixed> $options
     */
    private function hasAuthorizationHeader(array $options): bool
    {
        foreach ($options['headers'] ?? [] as $key => $value) {
            // Associative form: ['Authorization' => 'Bearer x'].
            if (\is_string($key) && 'authorization' === strtolower($key)) {
                return true;
            }

            // List form: ['Authorization: Bearer x'].
            if (\is_int($key) && \is_string($value) && str_starts_with(strtolower($value), 'authorization:')) {
                return true;
            }
        }

        return false;
    }

    private function failClosed(string $reason): never
    {
        $this->logger->error('ID token authentication failed closed.', ['reason' => $reason, 'base_uri' => $this->baseUri]);

        throw new IdTokenAuthMisconfiguredException($reason);
    }
}
