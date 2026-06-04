<?php

declare(strict_types=1);

namespace Tests\App\OAuth;

use App\Entity\Adherent;
use App\OAuth\AuthorizationCodeGenerator;
use App\Repository\OAuth\ClientRepository;
use League\OAuth2\Server\AuthorizationServer;
use PHPUnit\Framework\TestCase;
use Psr\Log\LoggerInterface;
use Symfony\Bridge\PsrHttpMessage\HttpMessageFactoryInterface;

class AuthorizationCodeGeneratorTest extends TestCase
{
    private const ADHERENT_UUID = 'a3f1c2d4-0000-4000-8000-000000000001';
    private const UNKNOWN_CLIENT_UUID = 'ffffffff-ffff-4fff-8fff-ffffffffffff';

    public function testMalformedClientIdLogsErrorAndReturnsNull(): void
    {
        // A client_id that is not even a UUID returns null silently in production: the account is
        // already activated upstream, so the front gets a 204 with no code and nothing to act on.
        // The error log is the only signal — without it the case is invisible on Sentry.
        $clientRepository = $this->createMock(ClientRepository::class);
        $clientRepository->expects(self::never())->method('findOneByUuid');

        $logger = $this->createMock(LoggerInterface::class);
        $logger
            ->expects(self::once())
            ->method('error')
            ->with(
                'Cannot mint an authorization code after signup activation: client_id is not a valid UUID.',
                self::callback(function (array $context): bool {
                    return self::ADHERENT_UUID === $context['adherent'] && 'not-a-uuid' === $context['client_id'];
                })
            )
        ;

        $generator = $this->createGenerator($clientRepository, $logger);

        self::assertNull($generator->generate($this->createAdherent(), 'challenge', 'not-a-uuid', 'http://localhost'));
    }

    public function testUnknownClientLogsErrorAndReturnsNull(): void
    {
        // A well-formed but unknown client_id has no client to bind the code to: same silent 204 in
        // production. This log distinguishes a stale/misconfigured client_id from a malformed one.
        $clientRepository = $this->createMock(ClientRepository::class);
        $clientRepository
            ->expects(self::once())
            ->method('findOneByUuid')
            ->with(self::UNKNOWN_CLIENT_UUID)
            ->willReturn(null)
        ;

        $logger = $this->createMock(LoggerInterface::class);
        $logger
            ->expects(self::once())
            ->method('error')
            ->with(
                'Cannot mint an authorization code after signup activation: no OAuth client matches the given client_id.',
                self::callback(function (array $context): bool {
                    return self::ADHERENT_UUID === $context['adherent'] && self::UNKNOWN_CLIENT_UUID === $context['client_id'];
                })
            )
        ;

        $generator = $this->createGenerator($clientRepository, $logger);

        self::assertNull($generator->generate($this->createAdherent(), 'challenge', self::UNKNOWN_CLIENT_UUID, 'http://localhost'));
    }

    private function createGenerator(ClientRepository $clientRepository, LoggerInterface $logger): AuthorizationCodeGenerator
    {
        // The authorization server and the PSR-7 factory are never reached on the early-return paths
        // under test, so plain stubs are enough.
        return new AuthorizationCodeGenerator(
            $this->createStub(AuthorizationServer::class),
            $clientRepository,
            $this->createStub(HttpMessageFactoryInterface::class),
            $logger,
        );
    }

    private function createAdherent(): Adherent
    {
        // A stub, not a mock: getUuidAsString() is only a fixed return, no interaction to verify.
        $adherent = $this->createStub(Adherent::class);
        $adherent->method('getUuidAsString')->willReturn(self::ADHERENT_UUID);

        return $adherent;
    }
}
