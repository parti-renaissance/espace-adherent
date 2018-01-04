<?php

namespace AppBundle\Repository\Timeline;

use AppBundle\Entity\Timeline\Measure;
use Doctrine\ORM\EntityRepository;

class ThemeRepository extends EntityRepository
{
    public function findRelatedThemesForMeasure(Measure $measure): array
    {
        return $this->createQueryBuilder('theme')
            ->select('theme')
            ->join('theme.measures', 'theme_measure')
            ->where('theme_measure.measure = :measure')
            ->setParameter('measure', $measure)
            ->getQuery()
            ->getResult()
        ;
    }
}
