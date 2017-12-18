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
            ->createNextActionsQueryBuilder($citizenProject)
            ->setMaxResults(1)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }

    public function findNextCitizenActionsForCitizenProject(CitizenProject $citizenProject, int $maxResults = 5): array
    {
        return $this
            ->createNextActionsQueryBuilder($citizenProject)
            ->setMaxResults($maxResults)
            ->getQuery()
            ->getResult()
        ;
    }

    protected function createNextActionsQueryBuilder(CitizenProject $citizenProject): QueryBuilder
    {
        return $this
            ->createQueryBuilder('action')
            ->where('action.citizenProject = :citizenProject')
            ->setParameter('citizenProject', $citizenProject)
            ->andWhere('action.beginAt > :now')
            ->setParameter('now', new \DateTime())
            ->andWhere('action.status = :status')
            ->setParameter('status', CitizenAction::STATUS_SCHEDULED)
            ->orderBy('action.beginAt', 'DESC')
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

    public function findOneCitizenActionBySlug(string $slug): ?CitizenAction
    {
        $query = $this
            ->createQueryBuilder('ca')
            ->select('ca', 'c', 'o')
            ->leftJoin('e.category', 'c')
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
