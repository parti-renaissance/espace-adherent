<?php

namespace AppBundle\Repository;

use AppBundle\Entity\BaseEvent;
use AppBundle\Entity\CitizenInitiative;
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

    public function countSitemapCitizenInitiatives(): int
    {
        return (int) $this
            ->createSitemapQueryBuilder()
            ->select('COUNT(ci) AS nb')
            ->getQuery()
            ->getSingleScalarResult()
        ;
    }

    /**
     * @return CitizenInitiative[]
     */
    public function findSitemapCitizenInitiatives(int $page, int $perPage): array
    {
        return $this
            ->createSitemapQueryBuilder()
            ->select('ci.uuid', 'ci.slug', 'ci.updatedAt')
            ->orderBy('ci.id')
            ->setFirstResult(($page - 1) * $perPage)
            ->setMaxResults($perPage)
            ->getQuery()
            ->getArrayResult()
        ;
    }

    private function createSitemapQueryBuilder(): QueryBuilder
    {
        return $this
            ->createQueryBuilder('ci')
            ->select('ci')
            ->where('ci.status = :status')
            ->andWhere('ci.published = :published')
            ->setParameter('status', BaseEvent::STATUS_SCHEDULED)
            ->setParameter('published', true)
        ;
    }
}
