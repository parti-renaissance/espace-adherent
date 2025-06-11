<?php

namespace App\Repository\Jecoute;

use App\Entity\Jecoute\News;
use App\Repository\GeoZoneTrait;
use App\Repository\UuidEntityRepositoryTrait;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

class NewsRepository extends ServiceEntityRepository
{
    use UuidEntityRepositoryTrait;
    use GeoZoneTrait;

    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, News::class);
    }

    public function listForZone(array $zones): array
    {
        return $this->createQueryBuilder('n')
            ->where('n.zone IN (:zones)')
            ->setParameter('zones', $zones)
            ->addOrderBy('n.createdAt', 'DESC')
            ->getQuery()
            ->getResult()
        ;
    }
}
