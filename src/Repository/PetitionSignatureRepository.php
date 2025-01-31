<?php

namespace App\Repository;

use App\Entity\PetitionSignature;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

class PetitionSignatureRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, PetitionSignature::class);
    }

    /**
     * @return PetitionSignature[]
     */
    public function findAllToRemind(): array
    {
        return $this->createQueryBuilder('ps')
            ->where('ps.validatedAt IS NULL AND ps.remindedAt IS NULL')
            ->andWhere('ps.createdAt < :date')
            ->setParameter('date', new \DateTime('-1 week'))
            ->getQuery()
            ->getResult()
        ;
    }
}
