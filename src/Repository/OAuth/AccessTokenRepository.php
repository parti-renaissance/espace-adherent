<?php

namespace AppBundle\Repository\OAuth;

use AppBundle\Entity\OAuth\AccessToken;
use AppBundle\Entity\OAuth\Client;
use Doctrine\ORM\EntityRepository;
use Ramsey\Uuid\UuidInterface;

class AccessTokenRepository extends EntityRepository
{
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
     * @param Client $client
     *
     * @return AccessToken[]
     */
    public function findAllAccessTokensByClient(Client $client): array
    {
        return $this->findBy(['client' => $client]);
    }

    public function revokeClientTokens(Client $client): void
    {
        foreach ($this->findAllAccessTokensByClient($client) as $accessToken) {
            if (!$accessToken->isRevoked()) {
                $accessToken->revoke();
            }
        }
    }
}
