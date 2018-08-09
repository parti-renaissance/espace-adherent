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

    public function findOneApprovedBySlug(string $slug): ?TurnkeyProject
    {
        return $this
            ->createQueryBuilder('project')
            ->where('project.slug = :slug')
            ->setParameter('slug', $slug)
            ->andWhere('project.isApproved = :approved')
            ->setParameter('approved', true)
            ->getQuery()
            ->getOneOrNullResult()
        ;
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

    public function countApprouvedProjects(): int
    {
        return $this
            ->createQueryBuilder('projects')
            ->select('COUNT(projects)')
            ->where('projects.isApproved = :approved')
            ->setParameter('approved', true)
            ->getQuery()
            ->getSingleScalarResult()
        ;
    }

    public function findPinned(int $excludedProjectId = null): ?TurnkeyProject
    {
        $qb = $this
            ->createQueryBuilder('project')
            ->where('project.isPinned = 1')
        ;

        if ($excludedProjectId) {
            $qb->andWhere('project.id != :id')
                ->setParameter('id', $excludedProjectId)
            ;
        }

        return $qb->getQuery()
            ->getOneOrNullResult()
        ;
    }
}
