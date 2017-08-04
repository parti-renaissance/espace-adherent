<?php

namespace AppBundle\Repository;

use AppBundle\Entity\Adherent;
use AppBundle\Entity\Committee;
use AppBundle\Entity\Event;
use AppBundle\Search\SearchParametersFilter;
use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\Query\ResultSetMapping;
use Doctrine\ORM\QueryBuilder;

class EventRepository extends EntityRepository
{
    use NearbyTrait;
    use UuidEntityRepositoryTrait {
        findOneByUuid as findOneByValidUuid;
    }

    public function count(bool $onlyPublished = true): int
    {
        $qb = $this
            ->createQueryBuilder('e')
            ->select('COUNT(e)')
        ;

        if ($onlyPublished) {
            $qb->where('e.published = :published')
                ->setParameter('published', true);
        }

        return (int) $qb->getQuery()
            ->getSingleScalarResult();
    }

    public function findOneBySlug(string $slug): ?Event
    {
        $query = $this
            ->createQueryBuilder('e')
            ->select('e', 'a', 'c', 'o')
            ->leftJoin('e.category', 'a')
            ->leftJoin('e.committee', 'c')
            ->leftJoin('e.organizer', 'o')
            ->where('e.slug = :slug')
            ->andWhere('e.published = :published')
            ->andWhere('c.status = :status')
            ->setParameter('slug', $slug)
            ->setParameter('published', true)
            ->setParameter('status', Committee::APPROVED)
            ->getQuery()
        ;

        return $query->getOneOrNullResult();
    }

    public function findMostRecentEvent(): ?Event
    {
        $query = $this
            ->createQueryBuilder('ce')
            ->where('ce.published = :published')
            ->setParameter('published', true)
            ->orderBy('ce.createdAt', 'DESC')
            ->setMaxResults(1)
            ->getQuery();

        return $query->getOneOrNullResult();
    }

    /**
     * {@inheritdoc}
     */
    public function findOneByUuid(string $uuid): ?Event
    {
        return $this->findOneByValidUuid($uuid);
    }

    public function findOnePublishedByUuid(string $uuid): ?Event
    {
        return $this
            ->createUuidQueryBuilder($uuid)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }

    public function findOneActiveByUuid(string $uuid): ?Event
    {
        return $this
            ->createUuidQueryBuilder($uuid)
            ->andWhere('e.status IN (:statuses)')
            ->setParameter('statuses', Event::ACTIVE_STATUSES)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }

    private function createUuidQueryBuilder(string $uuid): QueryBuilder
    {
        self::validUuid($uuid);

        return $this
            ->createQueryBuilder('e')
            ->select('e', 'a', 'c', 'o')
            ->leftJoin('e.category', 'a')
            ->leftJoin('e.committee', 'c')
            ->leftJoin('e.organizer', 'o')
            ->where('e.uuid = :uuid')
            ->setParameter('uuid', $uuid)
            ->andWhere('e.published = :published')
            ->setParameter('published', true)
        ;
    }

    /**
     * @return Event[]
     */
    public function findManagedBy(Adherent $referent): array
    {
        $qb = $this->createQueryBuilder('e')
            ->select('e', 'a', 'c', 'o')
            ->leftJoin('e.category', 'a')
            ->leftJoin('e.committee', 'c')
            ->leftJoin('e.organizer', 'o')
            ->where('e.published = :published')
            ->orderBy('e.beginAt', 'DESC')
            ->addOrderBy('e.name', 'ASC')
            ->setParameter('published', true)
        ;

        $codesFilter = $qb->expr()->orX();

        foreach ($referent->getManagedArea()->getCodes() as $key => $code) {
            if (is_numeric($code)) {
                // Postal code prefix
                $codesFilter->add(
                    $qb->expr()->andX(
                        'e.postAddress.country = \'FR\'',
                        $qb->expr()->like('e.postAddress.postalCode', ':code'.$key)
                    )
                );

                $qb->setParameter('code'.$key, $code.'%');
            } else {
                // Country
                $codesFilter->add($qb->expr()->eq('e.postAddress.country', ':code'.$key));
                $qb->setParameter('code'.$key, $code);
            }
        }

        $qb->andWhere($codesFilter);

        return $qb->getQuery()->getResult();
    }

    /**
     * @return Event[]
     */
    public function findUpcomingEvents(int $category = null): array
    {
        $qb = $this->createUpcomingEventsQueryBuilder();

        if ($category) {
            $qb->andWhere('a.id = :category')->setParameter('category', $category);
        }

        return $qb->getQuery()->getResult();
    }

    public function countUpcomingEvents(): int
    {
        return (int) $this
            ->createUpcomingEventsQueryBuilder()
            ->select('COUNT(e.id)')
            ->getQuery()
            ->getSingleScalarResult()
        ;
    }

    private function createUpcomingEventsQueryBuilder(): QueryBuilder
    {
        $qb = $this->createQueryBuilder('e');

        return $qb
            ->select('e', 'a', 'c', 'o')
            ->leftJoin('e.category', 'a')
            ->leftJoin('e.committee', 'c')
            ->leftJoin('e.organizer', 'o')
            ->where('e.published = :published')
            ->andWhere($qb->expr()->in('e.status', Event::ACTIVE_STATUSES))
            ->andWhere('e.beginAt >= :today')
            ->orderBy('e.beginAt', 'ASC')
            ->setParameter('published', true)
            ->setParameter('today', date('Y-m-d'))
        ;
    }

    public function countSitemapEvents(): int
    {
        return (int) $this
            ->createSitemapQueryBuilder()
            ->select('COUNT(c) AS nb')
            ->getQuery()
            ->getSingleScalarResult();
    }

    public function findSitemapEvents(int $page, int $perPage): array
    {
        return $this
            ->createSitemapQueryBuilder()
            ->select('e.uuid', 'e.slug', 'e.updatedAt')
            ->orderBy('e.id')
            ->setFirstResult(($page - 1) * $perPage)
            ->setMaxResults($perPage)
            ->getQuery()
            ->getArrayResult();
    }

    private function createSitemapQueryBuilder(): QueryBuilder
    {
        return $this
            ->createQueryBuilder('e')
            ->select('e', 'a', 'c', 'o')
            ->leftJoin('e.category', 'a')
            ->leftJoin('e.committee', 'c')
            ->leftJoin('e.organizer', 'o')
            ->where('c.status = :status')
            ->andWhere('e.published = :published')
            ->setParameter('status', Committee::APPROVED)
            ->setParameter('published', true)
        ;
    }

    /**
     * @return Event[]
     */
    public function searchEvents(SearchParametersFilter $search): array
    {
        if ($coordinates = $search->getCityCoordinates()) {
            $qb = $this
                ->createNearbyQueryBuilder($coordinates)
                ->andWhere($this->getNearbyExpression().' < :distance_max')
                ->andWhere('n.beginAt > :today')
                ->setParameter('distance_max', $search->getRadius())
                ->setParameter('today', new \DateTime('today'))
                ->orderBy('n.beginAt', 'asc')
                ->addOrderBy('distance_between', 'asc')
            ;
        } else {
            $qb = $this->createQueryBuilder('n');
        }

        $qb->andWhere('n.published = :published')
           ->setParameter('published', true);

        if (!empty($query = $search->getQuery())) {
            $qb->andWhere('n.name like :query');
            $qb->setParameter('query', '%'.$query.'%');
        }

        if ($category = $search->getEventCategory()) {
            $qb->andWhere('n.category = :category');
            $qb->setParameter('category', $category);
        }

        return $qb
            ->setFirstResult($search->getOffset())
            ->setMaxResults($search->getMaxResults())
            ->getQuery()
            ->getResult()
        ;
    }

    public function searchAllEvents(SearchParametersFilter $search): array
    {
        $sql = <<<'SQL'
SELECT *, (6371 * ACOS(COS(RADIANS(:latitude)) * COS(RADIANS(events.address_latitude)) * COS(RADIANS(events.address_longitude) - RADIANS(:longitude)) + SIN(RADIANS(:latitude)) * SIN(RADIANS(events.address_latitude)))) AS distance 
FROM events 
WHERE (events.address_latitude IS NOT NULL 
    AND events.address_longitude IS NOT NULL 
    AND (6371 * ACOS(COS(RADIANS(:latitude)) * COS(RADIANS(events.address_latitude)) * COS(RADIANS(events.address_longitude) - RADIANS(:longitude)) + SIN(RADIANS(:latitude)) * SIN(RADIANS(events.address_latitude)))) < :distance_max 
    AND events.begin_at > :today 
    AND events.published = :published) 
ORDER BY events.begin_at ASC, distance ASC 
LIMIT :max_results 
OFFSET :first_result
SQL;

        $rsm = new ResultSetMapping();
        $query = $this->getEntityManager()->createNativeQuery($sql, $rsm);

        if ($coordinates = $search->getCityCoordinates()) {
            $query->setParameter('distance_max', $search->getRadius());
            $query->setParameter('today', date_format(new \DateTime('today'), 'Y-m-d H:i:s'));
        }

        if (!empty($searchQuery = $search->getQuery())) {
            $query->setParameter('query', '%'.$searchQuery.'%');
        }

        if ($category = $search->getEventCategory()) {
            $query->setParameter('category', $category);
        }

        $query->setParameter('latitude', $search->getCityCoordinates()->getLatitude());
        $query->setParameter('longitude', $search->getCityCoordinates()->getLongitude());
        $query->setParameter('published', 1, \PDO::PARAM_INT);
        $query->setParameter('first_result', $search->getOffset(), \PDO::PARAM_INT);
        $query->setParameter('max_results', $search->getMaxResults(), \PDO::PARAM_INT);

        return $query->getResult('EventHydrator');
    }
}
