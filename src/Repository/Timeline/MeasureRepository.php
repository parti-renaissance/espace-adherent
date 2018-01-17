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
}
