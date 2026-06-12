<?php

declare(strict_types=1);

namespace Tests\App\OAuth;

use App\OAuth\TokenRequestErrorLogger;
use League\OAuth2\Server\Exception\OAuthServerException;
use Nyholm\Psr7\ServerRequest;
use PHPUnit\Framework\TestCase;
use Psr\Log\AbstractLogger;
use Psr\Log\LoggerInterface;
use Symfony\Component\RateLimiter\RateLimiterFactory;
use Symfony\Component\RateLimiter\Storage\InMemoryStorage;

class TokenRequestErrorLoggerTest extends TestCase
{
    public function testLogsClientErrorWithRedactedSensitiveDataAndResponseBody(): void
    {
        $logger = $this->createSpyLogger();
        $errorLogger = new TokenRequestErrorLogger($logger, $this->createLimiterFactory());

        $request = new ServerRequest('POST', 'https://app.parti-renaissance.fr/oauth/v2/token')
            ->withParsedBody([
                'grant_type' => 'authorization_code',
                'client_id' => 'a-public-client-id',
                'client_secret' => 'super-secret-value',
                'code' => 'the-authorization-code',
                'code_verifier' => 'the-pkce-verifier',
                'redirect_uri' => 'app://callback',
                'scope' => 'read',
            ])
            ->withHeader('Authorization', 'Basic dXNlcjpwYXNzd29yZA==')
            ->withHeader('User-Agent', 'RenaissanceApp/1.0')
        ;

        $errorLogger->logClientError($request, OAuthServerException::invalidGrant());

        self::assertCount(1, $logger->records);

        $record = $logger->records[0];
        self::assertSame('error', $record['level']);
        self::assertStringContainsString('invalid_grant', $record['message']);

        $context = $record['context'];
        self::assertSame('invalid_grant', $context['error_type']);
        self::assertSame(400, $context['status_code']);
        self::assertIsArray($context['response_body']);
        self::assertSame('invalid_grant', $context['response_body']['error']);

        $requestContext = $context['request'];
        self::assertSame('authorization_code', $requestContext['grant_type']);
        self::assertSame('a-public-client-id', $requestContext['client_id']);

        // Non-sensitive parameters are preserved.
        self::assertSame('a-public-client-id', $requestContext['parameters']['client_id']);
        self::assertSame('app://callback', $requestContext['parameters']['redirect_uri']);

        // Sensitive parameters are redacted but their presence is kept.
        self::assertSame('***', $requestContext['parameters']['client_secret']);
        self::assertSame('***', $requestContext['parameters']['code']);
        self::assertSame('***', $requestContext['parameters']['code_verifier']);

        // Sensitive headers are redacted, other headers are preserved.
        self::assertSame(['***'], $requestContext['headers']['Authorization']);
        self::assertSame(['RenaissanceApp/1.0'], $requestContext['headers']['User-Agent']);
    }

    public function testThrottlesLogsOnceTheLimitIsReached(): void
    {
        $logger = $this->createSpyLogger();
        $errorLogger = new TokenRequestErrorLogger($logger, $this->createLimiterFactory());

        $request = new ServerRequest('POST', 'https://app.parti-renaissance.fr/oauth/v2/token')
            ->withParsedBody(['grant_type' => 'authorization_code']);

        for ($i = 0; $i < 6; ++$i) {
            $errorLogger->logClientError($request, OAuthServerException::invalidGrant());
        }

        // The 6th call is throttled: the limiter allows only 5 per window.
        self::assertCount(5, $logger->records);
    }

    private function createLimiterFactory(): RateLimiterFactory
    {
        return new RateLimiterFactory(
            ['id' => 'oauth_token_error_log', 'policy' => 'fixed_window', 'limit' => 5, 'interval' => '1 day'],
            new InMemoryStorage()
        );
    }

    private function createSpyLogger(): LoggerInterface
    {
        return new class extends AbstractLogger {
            /** @var array<array{level: string, message: string, context: array}> */
            public array $records = [];

            public function log($level, $message, array $context = []): void
            {
                $this->records[] = [
                    'level' => (string) $level,
                    'message' => (string) $message,
                    'context' => $context,
                ];
            }
        };
    }
}
