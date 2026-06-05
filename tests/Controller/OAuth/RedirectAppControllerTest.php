<?php

declare(strict_types=1);

namespace Tests\App\Controller\OAuth;

use App\Controller\OAuth\RedirectAppController;
use App\Scope\ScopeGeneratorResolver;
use PHPUnit\Framework\TestCase;
use Psr\Log\LoggerInterface;

/**
 * Unit coverage for the SPA-host redirect-uri selection. The happy paths are proven end-to-end by the
 * functional SecurityControllerTest/CampaignSecurityControllerTest; this isolates the pure matching logic
 * and its defensive branches (empty SPA host, no match, multiple matches, non-http schemes) that are hard
 * to trigger functionally. The matcher stays a private method (single use, KISS), so it is exercised here
 * via reflection rather than by extracting a single-use helper class.
 */
class RedirectAppControllerTest extends TestCase
{
    public function testSelectsRegisteredUriMatchingSpaHost(): void
    {
        $logger = $this->createMock(LoggerInterface::class);
        $logger->expects(self::never())->method('warning');

        $uri = $this->resolve($logger, ['http://vox.code', 'http://campaign.code', 'vox-dev://'], 'campaign.code');

        self::assertSame('http://campaign.code', $uri);
    }

    public function testMatchesHostWithPort(): void
    {
        $logger = $this->createMock(LoggerInterface::class);
        $logger->expects(self::never())->method('warning');

        $uri = $this->resolve($logger, ['http://vox.code', 'http://localhost:8081'], 'localhost:8081');

        self::assertSame('http://localhost:8081', $uri);
    }

    public function testFallsBackToFirstUriWhenSpaHostIsEmpty(): void
    {
        $logger = $this->createMock(LoggerInterface::class);
        $logger->expects(self::never())->method('warning');

        $uri = $this->resolve($logger, ['http://vox.code', 'http://campaign.code'], '');

        self::assertSame('http://vox.code', $uri);
    }

    public function testFallsBackToFirstUriAndWarnsWhenNoUriMatches(): void
    {
        $logger = $this->createMock(LoggerInterface::class);
        $logger
            ->expects(self::once())
            ->method('warning')
            ->with(
                self::stringContains('No redirect URI matches'),
                self::callback(static fn (array $context): bool => 'unknown.code' === $context['spa_host'] && 'vox' === $context['app_code'])
            )
        ;

        $uri = $this->resolve($logger, ['http://vox.code', 'http://campaign.code'], 'unknown.code');

        self::assertSame('http://vox.code', $uri);
    }

    public function testWarnsAndReturnsFirstMatchWhenMultipleUrisMatch(): void
    {
        $logger = $this->createMock(LoggerInterface::class);
        $logger
            ->expects(self::once())
            ->method('warning')
            ->with(
                self::stringContains('Multiple redirect URIs'),
                self::callback(static fn (array $context): bool => 'dup.code' === $context['spa_host'])
            )
        ;

        $uri = $this->resolve($logger, ['http://dup.code', 'https://dup.code'], 'dup.code');

        self::assertSame('http://dup.code', $uri);
    }

    public function testIgnoresNonHttpSchemes(): void
    {
        $logger = $this->createMock(LoggerInterface::class);
        $logger->expects(self::never())->method('warning');

        // The vox-dev:// deep link shares the host but is not an http(s) callback: it must be skipped so a
        // single http match remains (no spurious multi-match warning).
        $uri = $this->resolve($logger, ['vox-dev://campaign.code', 'http://campaign.code'], 'campaign.code');

        self::assertSame('http://campaign.code', $uri);
    }

    /**
     * @param string[] $redirectUris
     */
    private function resolve(LoggerInterface $logger, array $redirectUris, string $spaHost, ?string $appCode = 'vox'): string
    {
        $controller = new RedirectAppController('admin.code', $this->createStub(ScopeGeneratorResolver::class), $logger);

        return new \ReflectionMethod($controller, 'resolveRedirectUriForSpaHost')
            ->invoke($controller, $redirectUris, $spaHost, $appCode);
    }
}
