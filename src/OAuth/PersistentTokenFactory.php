<?php

namespace App\OAuth;

use App\Entity\Adherent;
use App\Entity\Device;
use App\Entity\OAuth\AccessToken;
use App\Entity\OAuth\AuthorizationCode;
use App\Entity\OAuth\Client;
use App\Entity\OAuth\RefreshToken;
use App\OAuth\Model\DeviceAccessTokenInterface;
use App\Repository\AdherentRepository;
use App\Repository\DeviceRepository;
use App\Repository\OAuth\AccessTokenRepository;
use App\Repository\OAuth\ClientRepository;
use League\OAuth2\Server\Entities\AccessTokenEntityInterface;
use League\OAuth2\Server\Entities\AuthCodeEntityInterface;
use League\OAuth2\Server\Entities\RefreshTokenEntityInterface;
use League\OAuth2\Server\Entities\TokenInterface;
use Ramsey\Uuid\Uuid;
use Ramsey\Uuid\UuidInterface;

class PersistentTokenFactory
{
    private $accessTokenRepository;
    private $clientRepository;
    private $adherentRepository;
    private $deviceRepository;

    public function __construct(
        AccessTokenRepository $accessTokenRepository,
        ClientRepository $clientRepository,
        AdherentRepository $adherentRepository,
        DeviceRepository $deviceRepository
    ) {
        $this->accessTokenRepository = $accessTokenRepository;
        $this->clientRepository = $clientRepository;
        $this->adherentRepository = $adherentRepository;
        $this->deviceRepository = $deviceRepository;
    }

    public function createAccessToken(AccessTokenEntityInterface $token): AccessToken
    {
        // A user is not mandatory for the client credentials grant
        $user = $token->getUserIdentifier() ? $this->getUser($token->getUserIdentifier()) : null;
        $client = $this->getClient($token->getClient()->getIdentifier());

        $device = null;
        if ($token instanceof DeviceAccessTokenInterface) {
            $device = $token->getDeviceIdentifier() ? $this->getDevice($token->getDeviceIdentifier()) : null;
        }

        $accessToken = new AccessToken(
            $this->createTokenUuid($token->getIdentifier()),
            $user,
            $token->getIdentifier(),
            clone $token->getExpiryDateTime(),
            $client,
            $device
        );

        if ($device) {
            $device->login();
        }

        $accessToken->addScopes($this->extractScopes($token));

        return $accessToken;
    }

    public function createRefreshToken(RefreshTokenEntityInterface $token): RefreshToken
    {
        return new RefreshToken(
            $this->createTokenUuid($token->getIdentifier()),
            $this->accessTokenRepository->findAccessTokenByIdentifier($token->getAccessToken()->getIdentifier()),
            $token->getIdentifier(),
            clone $token->getExpiryDateTime()
        );
    }

    public function createAuthorizationCode(AuthCodeEntityInterface $token): AuthorizationCode
    {
        $user = $this->getUser($token->getUserIdentifier());
        $client = $this->getClient($token->getClient()->getIdentifier());

        $redirectUri = $token->getRedirectUri();
        if (!$redirectUri) {
            // If token has no redirect Uri, it means the client does not provide one because he has only one
            if (1 < \count($client->getRedirectUris())) {
                throw new \LogicException('Cannot determined which redirect URI to use. $token redirect_uri is empty and it falls back to client redirect_uri if and only if he has no more than one.');
            }

            $redirectUri = $client->getRedirectUris()[0];
        }

        $authCode = new AuthorizationCode(
            $this->createTokenUuid($token->getIdentifier()),
            $user,
            $token->getIdentifier(),
            clone $token->getExpiryDateTime(),
            $redirectUri,
            $client
        );

        $authCode->addScopes($this->extractScopes($token));

        return $authCode;
    }

    private function createTokenUuid(string $identifier): UuidInterface
    {
        return Uuid::uuid5(Uuid::NAMESPACE_OID, $identifier);
    }

    private function getUser(string $identifier): Adherent
    {
        if (!$user = $this->adherentRepository->findByUuid(Uuid::fromString($identifier))) {
            throw new \RuntimeException(sprintf('Unable to find %s entity by its identifier "%s".', Adherent::class, $identifier));
        }

        return $user;
    }

    private function getDevice(string $identifier): Device
    {
        if (!$device = $this->deviceRepository->findOneByDeviceUuid($identifier)) {
            throw new \RuntimeException(sprintf('Unable to find %s entity by its identifier "%s".', Device::class, $identifier));
        }

        return $device;
    }

    private function getClient(string $identifier): Client
    {
        if (!$client = $this->clientRepository->findClientByUuid(Uuid::fromString($identifier))) {
            throw new \RuntimeException(sprintf('Unable to find %s entity by its identifier "%s".', Client::class, $identifier));
        }

        return $client;
    }

    private function extractScopes(TokenInterface $token): array
    {
        $scopes = [];
        foreach ($token->getScopes() as $scope) {
            $scopes[] = $scope->getIdentifier();
        }

        return $scopes;
    }
}
