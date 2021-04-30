<?php

namespace App\Repository\OAuth;

use App\Entity\Adherent;
use App\Entity\OAuth\AccessToken;
use App\Entity\OAuth\Client;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use Ramsey\Uuid\UuidInterface;

class AccessTokenRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, AccessToken::class);
    }

    public function save(AccessToken $token): void
    {
        $this->_em->persist($token);
        $this->_em->flush();
    }

    public function findAccessTokenByIdentifier(string $identifier): ?AccessToken
    {
        return $this->findOneBy(['identifier' => $identifier]);
    }

    public function findAccessTokenByUuid(UuidInterface $uuid): ?AccessToken
    {
        return $this->findOneBy(['uuid' => $uuid->toString()]);
    }

    /**
     * @return AccessToken[]
     */
    public function findAllAccessTokensByClient(Client $client): array
    {
        return $this->findBy(['client' => $client]);
    }

    /**
     * @return AccessToken[]
     */
    public function findAllAccessTokensByUser(Adherent $user): array
    {
        return $this->findBy(['user' => $user]);
    }

    public function revokeClientTokens(Client $client): void
    {
        foreach ($this->findAllAccessTokensByClient($client) as $accessToken) {
            $this->revokeToken($accessToken);
        }
    }

    public function revokeUserTokens(Adherent $user): void
    {
        foreach ($this->findAllAccessTokensByUser($user) as $accessToken) {
            $this->revokeToken($accessToken);
        }
    }

    private function revokeToken(AccessToken $token): void
    {
        if (!$token->isRevoked()) {
            $token->revoke();
        }
    }
}
