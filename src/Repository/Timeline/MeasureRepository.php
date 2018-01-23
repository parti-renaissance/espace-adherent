<?php

namespace AppBundle\Repository\Timeline;

use AppBundle\Entity\Timeline\Measure;
use Doctrine\ORM\EntityRepository;

class MeasureRepository extends EntityRepository
{
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
            ->setParameter('status', $status)
            ->getQuery()
            ->getSingleScalarResult()
        ;
    }
}
