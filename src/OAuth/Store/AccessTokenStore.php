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
    use TokenStoreTrait;

    private $persistentTokenFactory;
    private $accessTokenRepository;

    public function __construct(
        PersistentTokenFactory $persistentTokenFactory,
        AccessTokenRepository $accessTokenRepository
    ) {
        $this->persistentTokenFactory = $persistentTokenFactory;
        $this->accessTokenRepository = $accessTokenRepository;
    }

    public function getNewToken(ClientEntityInterface $clientEntity, array $scopes, $userIdentifier = null)
    {
        $token = new InMemoryAccessToken();
        $token->setClient($clientEntity);
        $token->setUserIdentifier($userIdentifier);

        array_map([$token, 'addScope'], $scopes);

        return $token;
    }

    public function persistNewAccessToken(AccessTokenEntityInterface $accessToken)
    {
        $this->store($this->persistentTokenFactory->createAccessToken($accessToken));
    }

    public function revokeAccessToken($tokenId)
    {
        if (!$token = $this->findAccessToken($tokenId)) {
            return;
        }

        $this->checkToken($token);

        $token->revoke();
        $this->store($token);
    }

    public function isAccessTokenRevoked($tokenId)
    {
        if (!$token = $this->findAccessToken($tokenId)) {
            return true;
        }

        return $token->isRevoked();
    }

    public function findAccessToken(string $identifier): ?PersistentAccessToken
    {
        return $this->accessTokenRepository->findAccessTokenByIdentifier($identifier);
    }

    private function store(PersistentAccessToken $token): void
    {
        $this->accessTokenRepository->save($token);
    }
}
