<?php

namespace App\OAuth\Store;

use App\Entity\OAuth\AccessToken as PersistentAccessToken;
use App\OAuth\Model\AccessToken as InMemoryAccessToken;
use App\OAuth\PersistentTokenFactory;
use App\Repository\OAuth\AccessTokenRepository;
use League\OAuth2\Server\Entities\AccessTokenEntityInterface;
use League\OAuth2\Server\Entities\ClientEntityInterface;
use League\OAuth2\Server\Repositories\AccessTokenRepositoryInterface as OAuthAccessTokenRepository;

class AccessTokenStore implements OAuthAccessTokenRepository
{
    private $persistentTokenFactory;
    private $accessTokenRepository;

    public function __construct(
        PersistentTokenFactory $persistentTokenFactory,
        AccessTokenRepository $accessTokenRepository
    ) {
        $this->persistentTokenFactory = $persistentTokenFactory;
        $this->accessTokenRepository = $accessTokenRepository;
    }

    /**
     * {@inheritdoc}
     */
    public function getNewToken(ClientEntityInterface $clientEntity, array $scopes, $userIdentifier = null)
    {
        return new InMemoryAccessToken();
    }

    /**
     * {@inheritdoc}
     */
    public function persistNewAccessToken(AccessTokenEntityInterface $accessToken)
    {
        $this->store($this->persistentTokenFactory->createAccessToken($accessToken));
    }

    /**
     * {@inheritdoc}
     */
    public function revokeAccessToken($tokenId)
    {
        if (!$token = $this->findAccessToken($tokenId)) {
            return;
        }

        $token->revoke();
        $this->store($token);
    }

    /**
     * {@inheritdoc}
     */
    public function isAccessTokenRevoked($tokenId)
    {
        if (!$token = $this->findAccessToken($tokenId)) {
            return true;
        }

        return $token->isRevoked();
    }

    private function findAccessToken(string $identifier): ?PersistentAccessToken
    {
        return $this->accessTokenRepository->findAccessTokenByIdentifier($identifier);
    }

    private function store(PersistentAccessToken $token): void
    {
        $this->accessTokenRepository->save($token);
    }
}
