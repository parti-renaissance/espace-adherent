<?php

declare(strict_types=1);

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

    public function __construct(
        private readonly PersistentTokenFactory $persistentTokenFactory,
        private readonly AccessTokenRepository $accessTokenRepository,
    ) {
    }

    public function getNewToken(
        ClientEntityInterface $clientEntity,
        array $scopes,
        ?string $userIdentifier = null,
    ): AccessTokenEntityInterface {
        $token = new InMemoryAccessToken();
        $token->setClient($clientEntity);
        if (null !== $userIdentifier) {
            $token->setUserIdentifier($userIdentifier);
        }

        array_map([$token, 'addScope'], $scopes);

        return $token;
    }

    public function persistNewAccessToken(AccessTokenEntityInterface $accessTokenEntity): void
    {
        $this->store($this->persistentTokenFactory->createAccessToken($accessTokenEntity));
    }

    public function revokeAccessToken(string $tokenId): void
    {
        if (!$token = $this->findAccessToken($tokenId)) {
            return;
        }

        $this->checkIfTokenIsAlreadyRevoked($token);

        $token->revoke(terminateSession: false);
        $this->store($token);
    }

    public function isAccessTokenRevoked(string $tokenId): bool
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
