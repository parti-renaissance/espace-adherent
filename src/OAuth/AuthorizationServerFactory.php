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
use League\OAuth2\Server\Repositories\AccessTokenRepositoryInterface;
use League\OAuth2\Server\Repositories\AuthCodeRepositoryInterface;
use League\OAuth2\Server\Repositories\ClientRepositoryInterface;
use League\OAuth2\Server\Repositories\RefreshTokenRepositoryInterface;
use League\OAuth2\Server\Repositories\ScopeRepositoryInterface;

class AuthorizationServerFactory
{
    private $accessTokenRepository;
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
        AuthCodeRepositoryInterface $authCodeRepository,
        ClientRepositoryInterface $clientRepository,
        RefreshTokenRepositoryInterface $refreshTokenRepository,
        ScopeRepositoryInterface $scopeRepository,
        SymfonyLeagueEventListener $symfonyLeagueEventListener,
        CryptKey $privateKey,
        private readonly OidcBearerResponse $oidcBearerResponse,
        private readonly ClientRepository $entityClientRepository,
        string $encryptionKey,
        private readonly int $maxIdleTime,
        string $accessTokenTtlInterval,
        string $refreshTokenTtlInterval,
        string $authCodeTtlInterval = 'PT10M',
    ) {
        $this->accessTokenRepository = $accessTokenRepository;
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

        $this->symfonyLeagueEventListener->register($server);

        $accessTokenTtl = new \DateInterval($this->accessTokenTtlInterval);
        $refreshTokenTtl = new \DateInterval($this->refreshTokenTtlInterval);

        $server->enableGrantType($this->createAuthCodeGrant(), $accessTokenTtl);
        $server->enableGrantType($this->createRefreshTokenGrant($refreshTokenTtl), $accessTokenTtl);

        return $server;
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
        $grant = new OidcAuthCodeGrant(
            $this->entityClientRepository,
            $this->authCodeRepository,
            $this->refreshTokenRepository,
            new \DateInterval($this->authCodeTtlInterval),
        );
        // v8 backward-compat: legacy clients used the authorization_code flow without PKCE and without
        // client_secret (i.e. public clients). v9 enforces PKCE for public clients by default; the custom
        // OidcAuthCodeGrant::validateAuthorizationRequest() re-introduces PKCE enforcement for clients that
        // explicitly opt in via Client::isPkceRequired().
        $grant->disableRequireCodeChallengeForPublicClients();

        return $grant;
    }
}
