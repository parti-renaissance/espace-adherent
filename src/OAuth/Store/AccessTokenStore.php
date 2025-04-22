<?php

namespace App\OAuth\Store;

use App\Entity\Adherent;
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
        AccessTokenRepository $accessTokenRepository,
    ) {
        $this->persistentTokenFactory = $persistentTokenFactory;
        $this->accessTokenRepository = $accessTokenRepository;
    }

    public function getNewToken(ClientEntityInterface $clientEntity, array $scopes, $userIdentifier = null): InMemoryAccessToken
    {
        $token = new InMemoryAccessToken();
        $token->setClient($clientEntity);
        $token->setUserIdentifier($userIdentifier);

        array_map([$token, 'addScope'], $scopes);

        return $token;
    }

    public function persistNewAccessToken(AccessTokenEntityInterface $accessToken): void
    {
        $newToken = $this->persistentTokenFactory->createAccessToken($accessToken);
        $user = $newToken->getUser();

        if ($user instanceof Adherent) {
            $user->recordLastLoginTime();
        }

        $this->store($newToken);
    }

    public function revokeAccessToken($tokenId): void
    {
        if (!$token = $this->findAccessToken($tokenId)) {
            return;
        }

        $this->checkIfTokenIsAlreadyRevoked($token);

        $token->revoke(terminateSession: false);
        $this->store($token);
    }

    public function isAccessTokenRevoked($tokenId): bool
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
