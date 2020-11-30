<?php

namespace App\OAuth;

use App\OAuth\Grant\ClientCredentialsGrant;
use App\OAuth\Grant\PasswordGrant;
use App\Repository\DeviceRepository;
use League\OAuth2\Server\AuthorizationServer;
use League\OAuth2\Server\Grant\AuthCodeGrant;
use League\OAuth2\Server\Grant\GrantTypeInterface;
use League\OAuth2\Server\Grant\RefreshTokenGrant;
use League\OAuth2\Server\Repositories\AccessTokenRepositoryInterface;
use League\OAuth2\Server\Repositories\AuthCodeRepositoryInterface;
use League\OAuth2\Server\Repositories\ClientRepositoryInterface;
use League\OAuth2\Server\Repositories\RefreshTokenRepositoryInterface;
use League\OAuth2\Server\Repositories\ScopeRepositoryInterface;
use League\OAuth2\Server\Repositories\UserRepositoryInterface;

class AuthorizationServerFactory
{
    private $accessTokenRepository;
    private $userRepository;
    private $deviceRepository;
    private $clientRepository;
    private $scopeRepository;
    private $privateKey;
    private $encryptionKey;
    private $authCodeRepository;
    private $refreshTokenRepository;

    public function __construct(
        AccessTokenRepositoryInterface $accessTokenRepository,
        UserRepositoryInterface $userRepository,
        DeviceRepository $deviceRepository,
        AuthCodeRepositoryInterface $authCodeRepository,
        ClientRepositoryInterface $clientRepository,
        RefreshTokenRepositoryInterface $refreshTokenRepository,
        ScopeRepositoryInterface $scopeRepository,
        string $privateKey,
        string $encryptionKey
    ) {
        $this->accessTokenRepository = $accessTokenRepository;
        $this->userRepository = $userRepository;
        $this->deviceRepository = $deviceRepository;
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
        $refreshTokenTtl = new \DateInterval('P1M');

        $server->enableGrantType(
            new AuthCodeGrant($this->authCodeRepository, $this->refreshTokenRepository, new \DateInterval('PT10M')),
            $accessTokenTtl
        );

        $server->enableGrantType($this->createClientCredetialsGrant(), $accessTokenTtl);
        $server->enableGrantType($this->createRefreshTokenGrant($refreshTokenTtl), $accessTokenTtl);
        $server->enableGrantType($this->createPasswordGrant($refreshTokenTtl), $accessTokenTtl);

        return $server;
    }

    private function createClientCredetialsGrant(): GrantTypeInterface
    {
        return new ClientCredentialsGrant($this->deviceRepository);
    }

    private function createPasswordGrant(\DateInterval $refreshTokenTtl): GrantTypeInterface
    {
        $grant = new PasswordGrant($this->deviceRepository, $this->userRepository, $this->refreshTokenRepository);
        $grant->setRefreshTokenTTL($refreshTokenTtl);

        return $grant;
    }

    private function createRefreshTokenGrant(\DateInterval $refreshTokenTtl): GrantTypeInterface
    {
        $grant = new RefreshTokenGrant($this->refreshTokenRepository);
        $grant->setRefreshTokenTTL($refreshTokenTtl);

        return $grant;
    }
}
