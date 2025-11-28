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

    private $persistentTokenFactory;
    private $refreshTokenRepository;

    public function __construct(
        PersistentTokenFactory $persistentTokenFactory,
        RefreshTokenRepository $refreshTokenRepository,
    ) {
        $this->persistentTokenFactory = $persistentTokenFactory;
        $this->refreshTokenRepository = $refreshTokenRepository;
    }

    public function getNewRefreshToken()
    {
        return new InMemoryRefreshToken();
    }

    public function persistNewRefreshToken(RefreshTokenEntityInterface $refreshToken)
    {
        $this->store($this->persistentTokenFactory->createRefreshToken($refreshToken));
    }

    public function revokeRefreshToken($tokenId)
    {
        if (!$token = $this->findRefreshToken($tokenId)) {
            return;
        }

        $this->checkIfTokenIsAlreadyRevoked($token);

        $token->revoke();
        $this->store($token);
    }

    public function isRefreshTokenRevoked($tokenId)
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
