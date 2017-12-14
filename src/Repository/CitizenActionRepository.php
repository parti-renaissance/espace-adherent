<?php

namespace AppBundle\Repository;

use AppBundle\Entity\CitizenAction;
use AppBundle\Entity\CitizenProject;
use Doctrine\ORM\QueryBuilder;

class CitizenActionRepository extends EventRepository
{
    public function findNextCitizenActionForCitizenProject(CitizenProject $citizenProject): ?CitizenAction
    {
        return $this
            ->createQueryBuilder('action')
            ->where('action.citizenProject = :citizenProject')
            ->setParameter('citizenProject', $citizenProject)
            ->andWhere('action.beginAt > :now')
            ->setParameter('now', new \DateTime())
            ->orderBy('action.beginAt', 'DESC')
            ->setMaxResults(1)
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
