<?php

namespace App\Repository\Contribution;

use App\Entity\Adherent;
use App\Entity\Contribution\Contribution;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

class ContributionRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Contribution::class);
    }

    public function findLastAdherentContribution(Adherent $adherent): ?Contribution
    {
        return $this->createQueryBuilder('contribution')
            ->where('contribution.adherent = :adherent')
            ->setParameter('adherent', $adherent)
            ->orderBy('contribution.createdAt', 'DESC')
            ->setMaxResults(1)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
}
