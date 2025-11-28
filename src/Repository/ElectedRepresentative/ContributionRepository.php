<?php

declare(strict_types=1);

namespace App\Repository\ElectedRepresentative;

use App\Entity\Adherent;
use App\Entity\ElectedRepresentative\Contribution;
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
            ->innerJoin('contribution.electedRepresentative', 'elected_representative')
            ->where('elected_representative.adherent = :adherent')
            ->setParameter('adherent', $adherent)
            ->orderBy('contribution.createdAt', 'DESC')
            ->setMaxResults(1)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
}
