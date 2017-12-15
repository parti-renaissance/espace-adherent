<?php

namespace AppBundle\Repository;

use AppBundle\Entity\CitizenAction;
use AppBundle\Entity\CitizenProject;
use Doctrine\ORM\QueryBuilder;

class CitizenActionRepository extends EventRepository
{
    const TYPE_PAST = 'past';
    const TYPE_UPCOMING = 'upcoming';
    const TYPE_ALL = 'all';

    public function findNextCitizenActionForCitizenProject(CitizenProject $citizenProject): ?CitizenAction
    {
        return $this
            ->createQueryBuilder('a')
            ->where('a.citizenProject = :citizenProject')
            ->setParameter('citizenProject', $citizenProject)
            ->orderBy('a.beginAt', 'DESC')
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }

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
}
