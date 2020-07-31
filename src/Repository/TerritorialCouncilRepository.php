<?php

namespace App\Repository;

use App\Entity\ReferentTag;
use App\Entity\TerritorialCouncil\TerritorialCouncil;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Symfony\Bridge\Doctrine\RegistryInterface;

class TerritorialCouncilRepository extends ServiceEntityRepository
{
    public function __construct(RegistryInterface $registry)
    {
        parent::__construct($registry, TerritorialCouncil::class);
    }

    public function findOneByReferentTag(ReferentTag $referentTag): ?TerritorialCouncil
    {
        return $this->createQueryBuilder('tc')
            ->leftJoin('tc.referentTags', 'tag')
            ->where('tag.id = :tag')
            ->setParameter('tag', $referentTag)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
}
