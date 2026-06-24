<?php

declare(strict_types=1);

namespace App\OAuth;

use App\OAuth\Grant\OidcAuthCodeGrant;
use App\OAuth\Grant\RefreshTokenGrant;
use App\OAuth\Listener\SymfonyLeagueEventListener;
use App\OAuth\ResponseType\OidcBearerResponse;
use App\Repository\OAuth\ClientRepository;
use League\OAuth2\Server\AuthorizationServer;
use League\OAuth2\Server\CryptKey;
use League\OAuth2\Server\Grant\GrantTypeInterface;
use League\OAuth2\Server\Grant\PasswordGrant;
use League\OAuth2\Server\Repositories\AccessTokenRepositoryInterface;
use League\OAuth2\Server\Repositories\AuthCodeRepositoryInterface;
use League\OAuth2\Server\Repositories\ClientRepositoryInterface;
use League\OAuth2\Server\Repositories\RefreshTokenRepositoryInterface;
use League\OAuth2\Server\Repositories\ScopeRepositoryInterface;
use League\OAuth2\Server\Repositories\UserRepositoryInterface;
use Psr\Log\LoggerInterface;

class AuthorizationServerFactory
{
    private $accessTokenRepository;
    private $userRepository;
    private $clientRepository;
    private $scopeRepository;
    private $privateKey;
    private $encryptionKey;
    private $authCodeRepository;
    private $refreshTokenRepository;
    private $accessTokenTtlInterval;
    private $refreshTokenTtlInterval;
    private $authCodeTtlInterval;
    private SymfonyLeagueEventListener $symfonyLeagueEventListener;

    public function __construct(
        AccessTokenRepositoryInterface $accessTokenRepository,
        UserRepositoryInterface $userRepository,
        AuthCodeRepositoryInterface $authCodeRepository,
        ClientRepositoryInterface $clientRepository,
        RefreshTokenRepositoryInterface $refreshTokenRepository,
        ScopeRepositoryInterface $scopeRepository,
        SymfonyLeagueEventListener $symfonyLeagueEventListener,
        CryptKey $privateKey,
        private readonly OidcBearerResponse $oidcBearerResponse,
        private readonly ClientRepository $entityClientRepository,
        private readonly LoggerInterface $oauthLogger,
        string $encryptionKey,
        private readonly int $maxIdleTime,
        string $accessTokenTtlInterval,
        string $refreshTokenTtlInterval,
        string $authCodeTtlInterval = 'PT10M',
    ) {
        $this->accessTokenRepository = $accessTokenRepository;
        $this->userRepository = $userRepository;
        $this->clientRepository = $clientRepository;
        $this->scopeRepository = $scopeRepository;
        $this->privateKey = $privateKey;
        $this->encryptionKey = $encryptionKey;
        $this->authCodeRepository = $authCodeRepository;
        $this->refreshTokenRepository = $refreshTokenRepository;
        $this->accessTokenTtlInterval = $accessTokenTtlInterval;
        $this->refreshTokenTtlInterval = $refreshTokenTtlInterval;
        $this->authCodeTtlInterval = $authCodeTtlInterval;
        $this->symfonyLeagueEventListener = $symfonyLeagueEventListener;
    }

    public function createServer(): AuthorizationServer
    {
        $server = new AuthorizationServer(
            $this->clientRepository,
            $this->accessTokenRepository,
            $this->scopeRepository,
            $this->privateKey,
            $this->encryptionKey,
            $this->oidcBearerResponse,
        );

        $server->getEmitter()->useListenerProvider($this->symfonyLeagueEventListener);

        $accessTokenTtl = new \DateInterval($this->accessTokenTtlInterval);
        $refreshTokenTtl = new \DateInterval($this->refreshTokenTtlInterval);

        $server->enableGrantType($this->createAuthCodeGrant(), $accessTokenTtl);
        $server->enableGrantType($this->createRefreshTokenGrant($refreshTokenTtl), $accessTokenTtl);
        $server->enableGrantType($this->createPasswordGrant($refreshTokenTtl), $accessTokenTtl);

        return $server;
    }

    private function createPasswordGrant(\DateInterval $refreshTokenTtl): GrantTypeInterface
    {
        $grant = new PasswordGrant($this->userRepository, $this->refreshTokenRepository);
        $grant->setRefreshTokenTTL($refreshTokenTtl);

        return $grant;
    }

    private function createRefreshTokenGrant(\DateInterval $refreshTokenTtl): GrantTypeInterface
    {
        $grant = new RefreshTokenGrant($this->refreshTokenRepository);
        $grant->setRefreshTokenTTL($refreshTokenTtl);
        $grant->adminSessionTtl = $this->maxIdleTime;

        return $grant;
    }

    private function createAuthCodeGrant(): GrantTypeInterface
    {
        return new OidcAuthCodeGrant(
            $this->entityClientRepository,
            $this->authCodeRepository,
            $this->refreshTokenRepository,
            new \DateInterval($this->authCodeTtlInterval),
            $this->oauthLogger,
        );
    }
}
