<?php

namespace App\Repository;

use App\Entity\Adherent;
use App\Entity\ReferentSpaceAccessInformation;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

class ReferentSpaceAccessInformationRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, ReferentSpaceAccessInformation::class);
    }

    public function findByAdherent(Adherent $adherent, ?int $resultTtl = null): ?ReferentSpaceAccessInformation
    {
        return $this->createQueryBuilder('accessInfo')
            ->where('accessInfo.adherent = :adherent')
            ->setParameter('adherent', $adherent)
            ->getQuery()
            ->useResultCache((bool) $resultTtl, $resultTtl)
            ->getOneOrNullResult()
        ;
    }
}
