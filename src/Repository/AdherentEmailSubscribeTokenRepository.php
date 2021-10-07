<?php

namespace App\Repository;

use App\Entity\Adherent;
use App\Entity\AdherentEmailSubscribeToken;
use Doctrine\Persistence\ManagerRegistry;

class AdherentEmailSubscribeTokenRepository extends AbstractAdherentTokenRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, AdherentEmailSubscribeToken::class);
    }

    /** @return AdherentEmailSubscribeToken[] */
    public function findAllAvailable(Adherent $adherent): array
    {
        return $this->createQueryBuilder('token')
            ->where('token.adherentUuid = :adherent_uuid')
            ->andWhere('token.usedAt IS NULL')
            ->andWhere('token.expiredAt > NOW()')
            ->setParameters([
                'adherent_uuid' => $adherent->getUuid(),
            ])
            ->getQuery()
            ->getResult()
        ;
    }
}
