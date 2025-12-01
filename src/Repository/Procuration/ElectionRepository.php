<?php

declare(strict_types=1);

namespace App\Repository\Procuration;

use App\Entity\ProcurationV2\Election;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends \Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository<\App\Entity\ProcurationV2\Election>
 */
class ElectionRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Election::class);
    }

    public function findAllOrderedByRoundDates(): array
    {
        return $this->createQueryBuilder('election')
            ->innerJoin('election.rounds', 'round')
            ->addSelect('round')
            ->addOrderBy('round.date', 'DESC')
            ->getQuery()
            ->getResult()
        ;
    }
}
