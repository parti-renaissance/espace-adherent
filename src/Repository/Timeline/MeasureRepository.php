<?php

namespace AppBundle\Repository\Timeline;

use AppBundle\Entity\Timeline\Measure;
use Doctrine\ORM\EntityRepository;

class MeasureRepository extends EntityRepository
{
    public function findOneByTitle(string $title): ?Measure
    {
        return $this->createQueryBuilder('measure')
            ->join('measure.translations', 'translations')
            ->where('translations.title = :title')
            ->setParameter('title', $title)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
}
