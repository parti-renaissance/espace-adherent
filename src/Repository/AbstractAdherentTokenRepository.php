<?php

namespace App\Repository;

use App\Entity\AdherentExpirableTokenInterface;
use App\ValueObject\SHA1;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Ramsey\Uuid\Uuid;

abstract class AbstractAdherentTokenRepository extends ServiceEntityRepository
{
    /**
     * Returns the most recent token of an adherent.
     */
    public function findAdherentMostRecentKey(string $adherent): ?AdherentExpirableTokenInterface
    {
        $adherent = Uuid::fromString($adherent);

        $query = $this
            ->createQueryBuilder('t')
            ->where('t.adherentUuid = :uuid')
            ->orderBy('t.createdAt', 'DESC')
            ->setParameter('uuid', $adherent->toString())
            ->getQuery()
        ;

        return $query->getOneOrNullResult();
    }

    /**
     * Finds a AdherentToken instance by its token unique value.
     */
    public function findByToken(string $token): ?AdherentExpirableTokenInterface
    {
        $token = SHA1::fromString($token);

        return $this->findOneBy(['value' => $token->getHash()]);
    }
}
