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
    public function findActiveAccessTokensByUser(Adherent $user): array
    {
        return $this
            ->createQueryBuilder('at')
            ->where('at.user = :user')
            ->andWhere('at.revokedAt IS NULL')
            ->setParameter('user', $user)
            ->getQuery()
            ->getResult()
        ;
    }

    public function revokeClientTokens(Client $client): void
    {
        foreach ($this->findAllAccessTokensByClient($client) as $accessToken) {
            $this->revokeToken($accessToken);
        }
    }

    public function revokeUserTokens(Adherent $user): void
    {
        foreach ($this->findActiveAccessTokensByUser($user) as $accessToken) {
            $this->revokeToken($accessToken);
        }

        $this->_em->flush();
    }

    public function revokeToken(AccessToken $token, bool $flush = false): void
    {
        if (!$token->isRevoked()) {
            $token->revoke();
            if ($flush) {
                $this->_em->flush();
            }
        }
    }
}
