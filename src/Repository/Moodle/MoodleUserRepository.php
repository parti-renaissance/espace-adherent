<?php

declare(strict_types=1);

namespace App\Repository\Moodle;

use App\Entity\Moodle\User;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

class MoodleUserRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, User::class);
    }

    /**
     * @param int[] $adherentIds
     *
     * @return User[]
     */
    public function findByAdherentIds(array $adherentIds): array
    {
        return $this->createQueryBuilder('mu')
            ->innerJoin('mu.adherent', 'a')
            ->andWhere('a.id IN (:ids)')
            ->setParameter('ids', $adherentIds)
            ->getQuery()
            ->getResult();
    }
}
