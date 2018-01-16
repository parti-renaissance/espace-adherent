<?php

namespace AppBundle\Repository\Timeline;

use AppBundle\Entity\Timeline\Theme;
use Doctrine\ORM\EntityRepository;

class ThemeRepository extends EntityRepository
{
    public function findOneByTitle(string $title): ?Theme
    {
        $qb = $this
            ->createQueryBuilder('theme')
            ->join('theme.translations', 'translations')
        ;

        return $qb
            ->andWhere('translations.title = :title')
            ->setParameter('title', $title)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
}
