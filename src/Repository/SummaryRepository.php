<?php

namespace AppBundle\Repository;

use AppBundle\Entity\Adherent;
use AppBundle\Entity\Summary;
use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\QueryBuilder;

class SummaryRepository extends EntityRepository
{
    public function createQueryBuilderForAdherent(Adherent $adherent): QueryBuilder
    {
        return $this->createQueryBuilder('s')
            ->where('s.member = :member')
            ->setParameter('member', $adherent)
        ;
    }

    public function findOneForAdherent(Adherent $adherent): ?Summary
    {
        return $this->createQueryBuilderForAdherent($adherent)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
}
