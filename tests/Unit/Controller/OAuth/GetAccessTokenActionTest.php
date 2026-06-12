<?php

declare(strict_types=1);

namespace Tests\App\Unit\Controller\OAuth;

use App\Controller\OAuth\OAuthServerController;
use App\OAuth\TokenRequestErrorLogger;
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
    private TokenRequestErrorLogger&MockObject $tokenRequestErrorLogger;
    private OAuthServerController $controller;

    protected function setUp(): void
    {
        $this->authorizationServer = $this->createMock(AuthorizationServer::class);
        $this->logger = $this->createMock(LoggerInterface::class);
        $this->tokenRequestErrorLogger = $this->createMock(TokenRequestErrorLogger::class);

        $this->controller = new OAuthServerController(
            $this->authorizationServer,
            $this->createStub(HttpFoundationFactoryInterface::class),
            $this->logger,
            $this->tokenRequestErrorLogger,
        );
    }

    public function testServerErrorLogsItsUnderlyingCause(): void
    {
        $cause = new \RuntimeException('The EntityManager is closed.');

        $this->mockAuthorizationServerThrows(OAuthServerException::serverError('Boom', $cause));

        $this->logger
            ->expects($this->once())
            ->method('error')
            ->with(self::isString(), self::callback(static fn (array $context): bool => ($context['exception'] ?? null) === $cause))
        ;

        // Server errors go to the server-error logger, never to the client-error one.
        $this->tokenRequestErrorLogger->expects($this->never())->method('logClientError');

        $response = $this->controller->getAccessTokenAction(new ServerRequest('POST', '/oauth/v2/token'));

        self::assertSame(500, $response->getStatusCode());
    }

    public function testUnexpectedExceptionIsLoggedAndRethrown(): void
    {
        $exception = new \RuntimeException('id_token claim encoding failed');

        $this->mockAuthorizationServerThrows($exception);

        $this->logger
            ->expects($this->once())
            ->method('error')
            ->with(self::isString(), self::callback(static fn (array $context): bool => ($context['exception'] ?? null) === $exception))
        ;

        // A non-OAuth exception is rethrown without reaching the client-error logger.
        $this->tokenRequestErrorLogger->expects($this->never())->method('logClientError');

        $this->expectExceptionObject($exception);

        $this->controller->getAccessTokenAction(new ServerRequest('POST', '/oauth/v2/token'));
    }

    public function testClientErrorIsDelegatedToTheDedicatedLogger(): void
    {
        $exception = OAuthServerException::invalidRequest('grant_type');

        $this->mockAuthorizationServerThrows($exception);

        // Client errors must not reach the server-error logger.
        $this->logger->expects($this->never())->method('error');

        $this->tokenRequestErrorLogger
            ->expects($this->once())
            ->method('logClientError')
            ->with(self::isInstanceOf(ServerRequestInterface::class), self::identicalTo($exception))
        ;

        $response = $this->controller->getAccessTokenAction(new ServerRequest('POST', '/oauth/v2/token'));

        self::assertSame(400, $response->getStatusCode());
    }

    private function mockAuthorizationServerThrows(\Throwable $exception): void
    {
        $this->authorizationServer
            ->expects($this->once())
            ->method('respondToAccessTokenRequest')
            ->with(self::isInstanceOf(ServerRequestInterface::class), self::isInstanceOf(ResponseInterface::class))
            ->willThrowException($exception)
        ;
    }
}
