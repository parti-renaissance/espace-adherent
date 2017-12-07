<?php

namespace AppBundle\Repository;

use AppBundle\Entity\Timeline\Theme;
use Doctrine\ORM\EntityRepository;

class ThemeRepository extends EntityRepository
{
    public function findOneBySlug(string $slug): ?Theme
    {
        return $this->createQueryBuilder('a')
            ->select('a', 'm')
            ->leftJoin('a.media', 'm')
            ->where('a.slug = :slug')
            ->setParameter('slug', $slug)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
}
