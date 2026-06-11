<?php

declare(strict_types=1);

namespace Tests\App\Unit\Controller\OAuth;

use App\Controller\OAuth\OAuthServerController;
use League\OAuth2\Server\AuthorizationServer;
use League\OAuth2\Server\Exception\OAuthServerException;
use Nyholm\Psr7\ServerRequest;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Log\LoggerInterface;
use Symfony\Bridge\PsrHttpMessage\HttpFoundationFactoryInterface;

class GetAccessTokenActionTest extends TestCase
{
    private AuthorizationServer&MockObject $authorizationServer;
    private LoggerInterface&MockObject $logger;
    private OAuthServerController $controller;

    protected function setUp(): void
    {
        $this->authorizationServer = $this->createMock(AuthorizationServer::class);
        $this->logger = $this->createMock(LoggerInterface::class);

        $this->controller = new OAuthServerController(
            $this->authorizationServer,
            $this->createStub(HttpFoundationFactoryInterface::class),
            $this->logger,
        );
    }

    public function testServerErrorLogsItsUnderlyingCause(): void
    {
        $cause = new \RuntimeException('The EntityManager is closed.');

        $this->mockAuthorizationServerThrows(OAuthServerException::serverError('Boom', $cause));

        $this->logger
            ->expects(self::once())
            ->method('error')
            ->with(self::isString(), self::callback(static fn (array $context): bool => ($context['exception'] ?? null) === $cause))
        ;

        $response = $this->controller->getAccessTokenAction(new ServerRequest('POST', '/oauth/v2/token'));

        self::assertSame(500, $response->getStatusCode());
    }

    public function testUnexpectedExceptionIsLoggedAndRethrown(): void
    {
        $exception = new \RuntimeException('id_token claim encoding failed');

        $this->mockAuthorizationServerThrows($exception);

        $this->logger
            ->expects(self::once())
            ->method('error')
            ->with(self::isString(), self::callback(static fn (array $context): bool => ($context['exception'] ?? null) === $exception))
        ;

        $this->expectExceptionObject($exception);

        $this->controller->getAccessTokenAction(new ServerRequest('POST', '/oauth/v2/token'));
    }

    public function testClientErrorIsNotLogged(): void
    {
        $this->mockAuthorizationServerThrows(OAuthServerException::invalidRequest('grant_type'));

        $this->logger->expects(self::never())->method('error');

        $response = $this->controller->getAccessTokenAction(new ServerRequest('POST', '/oauth/v2/token'));

        self::assertSame(400, $response->getStatusCode());
    }

    private function mockAuthorizationServerThrows(\Throwable $exception): void
    {
        $this->authorizationServer
            ->expects(self::once())
            ->method('respondToAccessTokenRequest')
            ->with(self::isInstanceOf(ServerRequestInterface::class), self::isInstanceOf(ResponseInterface::class))
            ->willThrowException($exception)
        ;
    }
}
