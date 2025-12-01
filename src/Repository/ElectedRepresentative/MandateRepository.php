<?php

declare(strict_types=1);

namespace App\Repository\ElectedRepresentative;

use App\Entity\ElectedRepresentative\Mandate;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends \Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository<\App\Entity\ElectedRepresentative\Mandate>
 */
class MandateRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Mandate::class);
    }

    public function getMandatesForPoliticalFunction(int $electedRepresentativeId): array
    {
        return $this
            ->createQueryBuilder('mandate')
            ->andWhere('mandate.electedRepresentative = :elected_representative')
            ->setParameter('elected_representative', $electedRepresentativeId)
            ->orderBy('mandate.number', 'ASC')
            ->getQuery()
            ->getResult()
        ;
    }
}
