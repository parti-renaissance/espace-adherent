<?php

namespace AppBundle\OAuth;

use League\OAuth2\Server\AuthorizationServer;
use League\OAuth2\Server\Grant\AuthCodeGrant;
use League\OAuth2\Server\Grant\ClientCredentialsGrant;
use League\OAuth2\Server\Grant\RefreshTokenGrant;
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

    public function __construct(
        AccessTokenRepositoryInterface $accessTokenRepository,
        AuthCodeRepositoryInterface $authCodeRepository,
        ClientRepositoryInterface $clientRepository,
        RefreshTokenRepositoryInterface $refreshTokenRepository,
        ScopeRepositoryInterface $scopeRepository,
        string $privateKey,
        string $encryptionKey
    ) {
        $this->accessTokenRepository = $accessTokenRepository;
        $this->clientRepository = $clientRepository;
        $this->scopeRepository = $scopeRepository;
        $this->privateKey = $privateKey;
        $this->encryptionKey = $encryptionKey;
        $this->authCodeRepository = $authCodeRepository;
        $this->refreshTokenRepository = $refreshTokenRepository;
    }

    public function createServer(): AuthorizationServer
    {
        $server = new AuthorizationServer(
            $this->clientRepository,
            $this->accessTokenRepository,
            $this->scopeRepository,
            $this->privateKey,
            $this->encryptionKey
        );

        $accessTokenTtl = new \DateInterval('PT1H');

        $server->enableGrantType(
            new AuthCodeGrant($this->authCodeRepository, $this->refreshTokenRepository, new \DateInterval('PT10M')),
            $accessTokenTtl
        );

        $server->enableGrantType(new ClientCredentialsGrant(), $accessTokenTtl);

        $refreshTokenGrant = new RefreshTokenGrant($this->refreshTokenRepository);
        $refreshTokenGrant->setRefreshTokenTTL(new \DateInterval('P1M')); // new refresh tokens will expire after 1 month
        $server->enableGrantType($refreshTokenGrant, $accessTokenTtl);

        return $server;
    }
}
