<?php

namespace AppBundle\Repository\Timeline;

use AppBundle\Entity\Timeline\Theme;
use AppBundle\Repository\TranslatableRepositoryTrait;
use Doctrine\ORM\EntityRepository;

class ThemeRepository extends EntityRepository
{
    use TranslatableRepositoryTrait;

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
