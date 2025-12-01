<?php

declare(strict_types=1);

namespace App\Repository\OAuth;

use App\Entity\Adherent;
use App\Entity\OAuth\Client;
use App\Entity\OAuth\RefreshToken;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use Ramsey\Uuid\UuidInterface;

/**
 * @extends \Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository<\App\Entity\OAuth\RefreshToken>
 */
class RefreshTokenRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, RefreshToken::class);
    }

    public function save(RefreshToken $token): void
    {
        $this->_em->persist($token);
        $this->_em->flush();
    }

    public function findRefreshTokenByIdentifier(string $identifier): ?RefreshToken
    {
        return $this->findOneBy(['identifier' => $identifier]);
    }

    public function findRefreshTokenByUuid(UuidInterface $uuid): ?RefreshToken
    {
        return $this->findOneBy(['uuid' => $uuid->toString()]);
    }

    /**
     * @return RefreshToken[]
     */
    public function findAllRefreshTokensByClient(Client $client): array
    {
        return $this
            ->createQueryBuilder('rt')
            ->join('rt.accessToken', 'at')
            ->where('at.client = :client')
            ->setParameter('client', $client)
            ->getQuery()
            ->getResult()
        ;
    }

    /**
     * @return RefreshToken[]
     */
    public function findActiveRefreshTokensByUser(Adherent $user): array
    {
        return $this
            ->createQueryBuilder('rt')
            ->join('rt.accessToken', 'at')
            ->where('at.user = :user')
            ->andWhere('rt.revokedAt IS NULL')
            ->setParameter('user', $user)
            ->getQuery()
            ->getResult()
        ;
    }

    public function revokeClientTokens(Client $client): void
    {
        foreach ($this->findAllRefreshTokensByClient($client) as $refreshToken) {
            $this->revokeToken($refreshToken);
        }
    }

    public function revokeUserTokens(Adherent $user): void
    {
        foreach ($this->findActiveRefreshTokensByUser($user) as $refreshToken) {
            $this->revokeToken($refreshToken);
        }

        $this->_em->flush();
    }

    private function revokeToken(RefreshToken $token): void
    {
        if (!$token->isRevoked()) {
            $token->revoke();
        }
    }
}
