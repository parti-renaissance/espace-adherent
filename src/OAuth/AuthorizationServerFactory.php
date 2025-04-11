<?php

namespace App\OAuth;

use App\OAuth\Grant\ClientCredentialsGrant;
use App\OAuth\Grant\PasswordGrant;
use App\Repository\DeviceRepository;
use League\OAuth2\Server\AuthorizationServer;
use League\OAuth2\Server\CryptKey;
use League\OAuth2\Server\Grant\AuthCodeGrant;
use League\OAuth2\Server\Grant\GrantTypeInterface;
use League\OAuth2\Server\Grant\RefreshTokenGrant;
use League\OAuth2\Server\Repositories\AccessTokenRepositoryInterface;
use League\OAuth2\Server\Repositories\AuthCodeRepositoryInterface;
use League\OAuth2\Server\Repositories\ClientRepositoryInterface;
use League\OAuth2\Server\Repositories\RefreshTokenRepositoryInterface;
use League\OAuth2\Server\Repositories\ScopeRepositoryInterface;
use League\OAuth2\Server\Repositories\UserRepositoryInterface;

# TODO: remove
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
    private $accessTokenTtlInterval;
    private $refreshTokenTtlInterval;
    private $authCodeTtlInterval;

    public function __construct(
        AccessTokenRepositoryInterface $accessTokenRepository,
        UserRepositoryInterface $userRepository,
        DeviceRepository $deviceRepository,
        AuthCodeRepositoryInterface $authCodeRepository,
        ClientRepositoryInterface $clientRepository,
        RefreshTokenRepositoryInterface $refreshTokenRepository,
        ScopeRepositoryInterface $scopeRepository,
        CryptKey $privateKey,
        string $encryptionKey,
        string $accessTokenTtlInterval,
        string $refreshTokenTtlInterval,
        string $authCodeTtlInterval = 'PT10M',
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
        $this->accessTokenTtlInterval = $accessTokenTtlInterval;
        $this->refreshTokenTtlInterval = $refreshTokenTtlInterval;
        $this->authCodeTtlInterval = $authCodeTtlInterval;
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

        $accessTokenTtl = new \DateInterval($this->accessTokenTtlInterval);
        $refreshTokenTtl = new \DateInterval($this->refreshTokenTtlInterval);

        $server->enableGrantType($this->createAuthCodeGrant(), $accessTokenTtl);
        $server->enableGrantType($this->createClientCredentialsGrant(), $accessTokenTtl);
        $server->enableGrantType($this->createRefreshTokenGrant($refreshTokenTtl), $accessTokenTtl);
        $server->enableGrantType($this->createPasswordGrant($refreshTokenTtl), $accessTokenTtl);

        return $server;
    }

    private function createClientCredentialsGrant(): GrantTypeInterface
    {
        $grant = new ClientCredentialsGrant();
        $grant->setDeviceRepository($this->deviceRepository);

        return $grant;
    }

    private function createPasswordGrant(\DateInterval $refreshTokenTtl): GrantTypeInterface
    {
        $grant = new PasswordGrant($this->userRepository, $this->refreshTokenRepository);
        $grant->setDeviceRepository($this->deviceRepository);
        $grant->setRefreshTokenTTL($refreshTokenTtl);

        return $grant;
    }

    private function createRefreshTokenGrant(\DateInterval $refreshTokenTtl): GrantTypeInterface
    {
        $grant = new RefreshTokenGrant($this->refreshTokenRepository);
        $grant->setRefreshTokenTTL($refreshTokenTtl);

        return $grant;
    }

    private function createAuthCodeGrant(): GrantTypeInterface
    {
        return new AuthCodeGrant(
            $this->authCodeRepository,
            $this->refreshTokenRepository,
            new \DateInterval($this->authCodeTtlInterval)
        );
    }
}
