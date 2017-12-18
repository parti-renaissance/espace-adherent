<?php

namespace AppBundle\Repository;

use Doctrine\ORM\QueryBuilder;
use AppBundle\Entity\CitizenAction;

class CitizenActionRepository extends EventRepository
{
    const TYPE_PAST = 'past';
    const TYPE_UPCOMING = 'upcoming';
    const TYPE_ALL = 'all';

    protected function createSlugQueryBuilder(string $slug): QueryBuilder
    {
        return $this
            ->createQueryBuilder('e')
            ->select('e', 'c', 'o')
            ->leftJoin('e.category', 'c')
            ->leftJoin('e.organizer', 'o')
            ->where('e.slug = :slug')
            ->setParameter('slug', $slug)
            ->andWhere('e.published = :published')
            ->setParameter('published', true)
        ;
    }

    public function findOneCitizenActionBySlug(string $slug): ?CitizenAction
    {
        $query = $this
            ->createQueryBuilder('e')
            ->select('e', 'a', 'o')
            ->leftJoin('e.category', 'a')
            ->leftJoin('e.organizer', 'o')
            ->where('e.slug = :slug')
            ->andWhere('e.published = :published')
            ->setParameter('slug', $slug)
            ->setParameter('published', true)
            ->getQuery()
        ;

        return $query->getOneOrNullResult();
    }
}
