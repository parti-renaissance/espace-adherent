<?php

declare(strict_types=1);

namespace App\Repository\AdherentFormation;

use App\Entity\AdherentFormation\Formation;
use App\Scope\ScopeVisibilityEnum;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\QueryBuilder;
use Doctrine\Persistence\ManagerRegistry;

class FormationRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Formation::class);
    }

    public function findAllNational(): array
    {
        return $this->createPublishedQueryBuilder()
            ->andWhere('formation.visibility = :visibility_national')
            ->setParameter('visibility_national', ScopeVisibilityEnum::NATIONAL)
            ->getQuery()
            ->getResult()
        ;
    }

    public function findAllLocal(array $zones): array
    {
        return $this->createPublishedQueryBuilder()
            ->andWhere('formation.visibility = :visibility_local')
            ->setParameter('visibility_local', ScopeVisibilityEnum::LOCAL)
            ->leftJoin('formation.zone', 'zone')
            ->leftJoin('zone.children', 'child')
            ->andWhere('zone IN (:zones) OR child IN (:zones)')
            ->setParameter('zones', $zones)
            ->getQuery()
            ->getResult()
        ;
    }

    public function findOnePublished(string $uuid): ?Formation
    {
        return $this->createPublishedQueryBuilder()
            ->andWhere('formation.uuid = :uuid')
            ->setParameter('uuid', $uuid)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }

    private function createPublishedQueryBuilder(): QueryBuilder
    {
        return $this
            ->createQueryBuilder('formation')
            ->andWhere('formation.published = TRUE')
            ->andWhere('formation.valid = TRUE')
            ->orderBy('formation.position', 'ASC')
        ;
    }
}
