<?php

namespace AppBundle\Repository;

use Doctrine\ORM\QueryBuilder;

class CitizenInitiativeRepository extends EventRepository
{
    protected function createSlugQueryBuilder(string $slug): QueryBuilder
    {
        return $this
            ->createQueryBuilder('e')
            ->select('e', 'a', 'o')
            ->leftJoin('e.citizenInitiativeCategory', 'a')
            ->leftJoin('e.organizer', 'o')
            ->where('e.slug = :slug')
            ->setParameter('slug', $slug)
            ->andWhere('e.published = :published')
            ->setParameter('published', true)
            ;
    }
}
