<?php

namespace AppBundle\Repository;

use Doctrine\ORM\QueryBuilder;

class MoocEventRepository extends EventRepository
{
    const TYPE_PAST = 'past';
    const TYPE_UPCOMING = 'upcoming';
    const TYPE_ALL = 'all';

    protected function createSlugQueryBuilder(string $slug): QueryBuilder
    {
        return $this
            ->createQueryBuilder('e')
            ->select('e', 'c', 'o')
            ->leftJoin('e.moocEventCategory', 'c')
            ->leftJoin('e.organizer', 'o')
            ->where('e.slug = :slug')
            ->setParameter('slug', $slug)
            ->andWhere('e.published = :published')
            ->setParameter('published', true)
        ;
    }
}
