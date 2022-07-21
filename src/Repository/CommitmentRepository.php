<?php

namespace App\Repository;

use App\Entity\Commitment;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

class CommitmentRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Commitment::class);
    }

    /**
     * @return Commitment[]
     */
    public function getAllOrdered(): array
    {
        return $this->createQueryBuilder('commitment')
            ->orderBy('commitment.position', 'ASC')
            ->getQuery()
            ->getResult()
        ;
    }
}
