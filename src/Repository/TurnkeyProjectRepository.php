<?php

namespace AppBundle\Repository;

use AppBundle\Entity\TurnkeyProject;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Symfony\Bridge\Doctrine\RegistryInterface;

class TurnkeyProjectRepository extends ServiceEntityRepository
{
    public function __construct(RegistryInterface $registry)
    {
        parent::__construct($registry, TurnkeyProject::class);
    }

    public function countProjects(): int
    {
        return $this
            ->createQueryBuilder('projects')
            ->select('COUNT(projects)')
            ->getQuery()
            ->getSingleScalarResult()
        ;
    }

    public function findPinned(int $excludedProjectId = null): ?TurnkeyProject
    {
        $qb = $this
            ->createQueryBuilder('p')
            ->where('p.isPinned = 1')
        ;

        if ($excludedProjectId) {
            $qb->andWhere('p.id != :id')
                ->setParameter('id', $excludedProjectId)
            ;
        }

        return $qb->getQuery()
            ->getOneOrNullResult()
        ;
    }
}
