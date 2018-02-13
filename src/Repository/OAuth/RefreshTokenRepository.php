<?php

namespace AppBundle\Repository\OAuth;

use AppBundle\Entity\OAuth\Client;
use AppBundle\Entity\OAuth\RefreshToken;
use Doctrine\ORM\EntityRepository;
use Ramsey\Uuid\UuidInterface;

class RefreshTokenRepository extends EntityRepository
{
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
     * @param Client $client
     *
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

    public function revokeClientTokens(Client $client): void
    {
        foreach ($this->findAllRefreshTokensByClient($client) as $refreshToken) {
            if (!$refreshToken->isRevoked()) {
                $refreshToken->revoke();
            }
        }
    }
}
