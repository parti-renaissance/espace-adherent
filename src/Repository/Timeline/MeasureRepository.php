<?php

namespace AppBundle\Repository\Timeline;

use AppBundle\Entity\Timeline\Measure;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Symfony\Bridge\Doctrine\RegistryInterface;

class MeasureRepository extends ServiceEntityRepository
{
    public function __construct(RegistryInterface $registry)
    {
        parent::__construct($registry, Measure::class);
    }

    public function findOneByTitle(string $title): ?Measure
    {
        $qb = $this
            ->createQueryBuilder('measure')
            ->join('measure.translations', 'translations')
        ;

        return $qb
            ->andWhere('translations.title = :title')
            ->setParameter('title', $title)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }

    public function countMeasuresByStatus(string $status): int
    {
        return $this
            ->createQueryBuilder('measure')
            ->select('COUNT(measure)')
            ->andWhere('measure.status = :status')
            ->andWhere('measure.major = true')
            ->setParameter('status', $status)
            ->getQuery()
            ->getSingleScalarResult()
        ;
    }
}
