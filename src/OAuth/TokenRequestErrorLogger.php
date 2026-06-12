<?php

declare(strict_types=1);

namespace App\OAuth;

use League\OAuth2\Server\Exception\OAuthServerException;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Log\LoggerInterface;
use Symfony\Component\RateLimiter\RateLimiterFactory;

/**
 * Logs client errors (4xx) returned by the OAuth token endpoint so they can be
 * investigated in Sentry, while throttling the volume to avoid flooding.
 *
 * Sensitive request data (credentials, codes) is redacted before logging.
 */
class TokenRequestErrorLogger
{
    /**
     * Request body parameters whose value must never reach the logs.
     */
    private const REDACTED_PARAMETERS = [
        'client_secret',
        'password',
        'code',
        'refresh_token',
        'code_verifier',
        'assertion',
    ];

    /**
     * Request headers whose value must never reach the logs.
     */
    private const REDACTED_HEADERS = [
        'authorization',
        'cookie',
        'proxy-authorization',
    ];

    private const REDACTED_PLACEHOLDER = '***';

    public function __construct(
        private readonly LoggerInterface $logger,
        private readonly RateLimiterFactory $oauthTokenErrorLogLimiter,
    ) {
    }

    public function logClientError(ServerRequestInterface $request, OAuthServerException $exception): void
    {
        if (!$this->oauthTokenErrorLogLimiter->create('global')->consume()->isAccepted()) {
            return;
        }

        $this->logger->error(
            \sprintf('OAuth token endpoint rejected the request (%s).', $exception->getErrorType()),
            [
                'error_type' => $exception->getErrorType(),
                'status_code' => $exception->getHttpStatusCode(),
                'hint' => $exception->getHint(),
                'response_body' => $exception->getPayload(),
                'request' => $this->buildRequestContext($request),
            ]
        );
    }

    private function buildRequestContext(ServerRequestInterface $request): array
    {
        $parsedBody = $request->getParsedBody();
        $parameters = \is_array($parsedBody) ? $parsedBody : [];

        return [
            'method' => $request->getMethod(),
            'uri' => (string) $request->getUri(),
            'grant_type' => $parameters['grant_type'] ?? null,
            'client_id' => $parameters['client_id'] ?? null,
            'redirect_uri' => $parameters['redirect_uri'] ?? null,
            'scope' => $parameters['scope'] ?? null,
            'parameters' => $this->redactParameters($parameters),
            'headers' => $this->redactHeaders($request->getHeaders()),
        ];
    }

    private function redactParameters(array $parameters): array
    {
        foreach (self::REDACTED_PARAMETERS as $key) {
            if (isset($parameters[$key])) {
                $parameters[$key] = self::REDACTED_PLACEHOLDER;
            }
        }

        return $parameters;
    }

    /**
     * @param array<string, string[]> $headers
     *
     * @return array<string, string[]>
     */
    private function redactHeaders(array $headers): array
    {
        foreach ($headers as $name => $values) {
            if (\in_array(strtolower($name), self::REDACTED_HEADERS, true)) {
                $headers[$name] = [self::REDACTED_PLACEHOLDER];
            }
        }

        return $headers;
    }
}
