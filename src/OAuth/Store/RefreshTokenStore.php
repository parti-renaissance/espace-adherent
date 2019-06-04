<?php

namespace AppBundle\OAuth\Store;

use AppBundle\Entity\OAuth\RefreshToken as PersistentRefreshToken;
use AppBundle\OAuth\Model\RefreshToken as InMemoryRefreshToken;
use AppBundle\OAuth\PersistentTokenFactory;
use AppBundle\Repository\OAuth\RefreshTokenRepository;
use League\OAuth2\Server\Entities\RefreshTokenEntityInterface;
use League\OAuth2\Server\Repositories\RefreshTokenRepositoryInterface as  OAuthRefreshTokenRepositoryInterface;

class RefreshTokenStore implements OAuthRefreshTokenRepositoryInterface
{
    private $persistentTokenFactory;
    private $refreshTokenRepository;

    public function __construct(
        PersistentTokenFactory $persistentTokenFactory,
        RefreshTokenRepository $refreshTokenRepository
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

    /**
     * {@inheritdoc}
     */
    public function revokeRefreshToken($tokenId)
    {
        if (!$token = $this->findRefreshToken($tokenId)) {
            return;
        }

        $token->revoke();
        $this->store($token);
    }

    /**
     * {@inheritdoc}
     */
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
