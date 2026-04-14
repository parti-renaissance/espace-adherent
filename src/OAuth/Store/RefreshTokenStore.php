<?php

declare(strict_types=1);

namespace App\OAuth\Store;

use App\Entity\OAuth\RefreshToken as PersistentRefreshToken;
use App\OAuth\Model\RefreshToken as InMemoryRefreshToken;
use App\OAuth\PersistentTokenFactory;
use App\Repository\OAuth\RefreshTokenRepository;
use League\OAuth2\Server\Entities\RefreshTokenEntityInterface;
use League\OAuth2\Server\Repositories\RefreshTokenRepositoryInterface as OAuthRefreshTokenRepositoryInterface;

class RefreshTokenStore implements OAuthRefreshTokenRepositoryInterface
{
    use TokenStoreTrait;

    public function __construct(
        private readonly PersistentTokenFactory $persistentTokenFactory,
        private readonly RefreshTokenRepository $refreshTokenRepository,
    ) {
    }

    public function getNewRefreshToken(): ?RefreshTokenEntityInterface
    {
        return new InMemoryRefreshToken();
    }

    public function persistNewRefreshToken(RefreshTokenEntityInterface $refreshTokenEntity): void
    {
        $this->store($this->persistentTokenFactory->createRefreshToken($refreshTokenEntity));
    }

    public function revokeRefreshToken(string $tokenId): void
    {
        if (!$token = $this->findRefreshToken($tokenId)) {
            return;
        }

        $this->checkIfTokenIsAlreadyRevoked($token);

        $token->revoke();
        $this->store($token);
    }

    public function isRefreshTokenRevoked(string $tokenId): bool
    {
        if (!$token = $this->findRefreshToken($tokenId)) {
            return true;
        }

        return $token->isRevoked();
    }

    private function findRefreshToken(string $identifier): ?PersistentRefreshToken
    {
        return $this->refreshTokenRepository->findRefreshTokenByIdentifier($identifier);
    }

    private function store(PersistentRefreshToken $token): void
    {
        $this->refreshTokenRepository->save($token);
    }
}
