<?php

namespace AppBundle\Repository;

use AppBundle\Entity\Adherent;
use AppBundle\Entity\BaseEvent;
use AppBundle\Entity\Committee;
use AppBundle\Entity\Event;
use AppBundle\Search\SearchParametersFilter;
use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\Query\ResultSetMapping;
use Doctrine\ORM\QueryBuilder;

class EventRepository extends EntityRepository
{
    const TYPE_PAST = 'past';
    const TYPE_UPCOMING = 'upcoming';
    const TYPE_ALL = 'all';

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
    public function findOneByUuid(string $uuid): ?BaseEvent
    {
        return $this->findOneByValidUuid($uuid);
    }

    public function findOnePublishedBySlug(string $slug): ?BaseEvent
    {
        return $this
            ->createSlugQueryBuilder($slug)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }

    public function findOneActiveBySlug(string $slug): ?BaseEvent
    {
        return $this
            ->createSlugQueryBuilder($slug)
            ->andWhere('e.status IN (:statuses)')
            ->setParameter('statuses', Event::ACTIVE_STATUSES)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }

    protected function createSlugQueryBuilder(string $slug): QueryBuilder
    {
        return $this
            ->createQueryBuilder('e')
            ->select('e', 'a', 'c', 'o')
            ->leftJoin('e.category', 'a')
            ->leftJoin('e.committee', 'c')
            ->leftJoin('e.organizer', 'o')
            ->where('e.slug = :slug')
            ->setParameter('slug', $slug)
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

    public function findEventsByOrganizer(Adherent $organizer): array
    {
        $query = $this
            ->createQueryBuilder('e')
            ->andWhere('e.organizer = :organizer')
            ->setParameter('organizer', $organizer)
            ->orderBy('e.createdAt', 'DESC')
            ->getQuery()
        ;

        return $query->getResult();
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
SELECT events.uuid AS event_uuid, events.organizer_id AS event_organizer_id, events.committee_id AS event_committee_id, 
events.name AS event_name, events.category_id AS event_category_id, events.description AS event_description, 
events.begin_at AS event_begin_at, events.finish_at AS event_finish_at, 
events.capacity AS event_capacity, events.is_for_legislatives AS event_is_for_legislatives, 
events.created_at AS event_created_at, events.participants_count AS event_participants_count, events.slug AS event_slug,
events.type AS event_type, events.address_address AS event_address_address, 
events.address_country AS event_address_country, events.address_city_name AS event_address_city_name, 
events.address_city_insee AS event_address_city_insee, events.address_postal_code AS event_address_postal_code, 
events.address_latitude AS event_address_latitude, events.address_longitude AS event_address_longitude, 
committees.uuid AS committee_uuid, committees.name AS committee_name, 
committees.description AS committee_description, committees.created_by AS committee_created_by, 
committees.address_address AS committee_address_address, committees.address_country AS committee_address_country, 
committees.address_city_name AS committee_address_city_name, committees.address_city_insee AS committee_address_city_insee, 
committees.address_postal_code AS committee_address_postal_code, committees.address_latitude AS committee_address_latitude, 
committees.address_longitude AS committee_address_longitude, adherents.uuid AS adherent_uuid, 
adherents.email_address AS adherent_email_address, adherents.password AS adherent_password, adherents.old_password AS adherent_old_password, 
adherents.gender AS adherent_gender, adherents.first_name AS adherent_first_name, 
adherents.last_name AS adherent_last_name, adherents.birthdate AS adherent_birthdate, 
adherents.managed_area_codes AS adherent_managed_area_codes, 
adherents.managed_area_marker_latitude AS adherent_managed_area_marker_latitude, 
adherents.managed_area_marker_longitude AS adherent_managed_area_marker_longitude, 
adherents.address_address AS adherent_address_address, adherents.address_country AS adherent_address_country, 
adherents.address_city_name AS adherent_address_city_name, adherents.address_city_insee AS adherent_address_city_insee, 
adherents.address_postal_code AS adherent_address_postal_code, adherents.address_latitude AS adherent_address_latitude, 
adherents.address_longitude AS adherent_address_longitude, adherents.position AS adherent_position,
(6371 * ACOS(COS(RADIANS(:latitude)) * COS(RADIANS(events.address_latitude)) * COS(RADIANS(events.address_longitude) - RADIANS(:longitude)) + SIN(RADIANS(:latitude)) * SIN(RADIANS(events.address_latitude)))) AS distance 
FROM events 
LEFT JOIN adherents ON adherents.id = events.organizer_id
LEFT JOIN committees ON committees.id = events.committee_id
LEFT JOIN events_categories ON events_categories.id = events.category_id
WHERE (events.address_latitude IS NOT NULL 
    AND events.address_longitude IS NOT NULL 
    AND (6371 * ACOS(COS(RADIANS(:latitude)) * COS(RADIANS(events.address_latitude)) * COS(RADIANS(events.address_longitude) - RADIANS(:longitude)) + SIN(RADIANS(:latitude)) * SIN(RADIANS(events.address_latitude)))) < :distance_max 
    AND events.begin_at > :today 
    AND events.published = :published
    AND events.status = :scheduled) 
    __filter_query__ 
    __filter_category__ 
ORDER BY events.begin_at ASC, distance ASC 
LIMIT :max_results 
OFFSET :first_result
SQL;

        if (!empty($searchQuery = $search->getQuery())) {
            $filterQuery = 'AND events.name like :query';
        } else {
            $filterQuery = '';
        }

        if ($category = $search->getEventCategory()) {
            $filterCategory = 'AND events.category_id = :category';
        } else {
            $filterCategory = '';
        }

        $sql = preg_replace(
            ['/__filter_query__/', '/__filter_category__/'],
            [$filterQuery, $filterCategory],
            $sql
        );

        $rsm = new ResultSetMapping();
        $query = $this->getEntityManager()->createNativeQuery($sql, $rsm);

        if ($search->getCityCoordinates()) {
            $query->setParameter('distance_max', $search->getRadius());
            $query->setParameter('today', date_format(new \DateTime('today'), 'Y-m-d H:i:s'));
        }

        if (!empty($searchQuery)) {
            $query->setParameter('query', '%'.$searchQuery.'%');
        }

        if ($category) {
            $query->setParameter('category', $category);
        }

        $query->setParameter('latitude', $search->getCityCoordinates()->getLatitude());
        $query->setParameter('longitude', $search->getCityCoordinates()->getLongitude());
        $query->setParameter('published', 1, \PDO::PARAM_INT);
        $query->setParameter('scheduled', BaseEvent::STATUS_SCHEDULED);
        $query->setParameter('first_result', $search->getOffset(), \PDO::PARAM_INT);
        $query->setParameter('max_results', $search->getMaxResults(), \PDO::PARAM_INT);

        return $query->getResult('EventHydrator');
    }

    public function removeOrganizerEvents(Adherent $organizer, string $type = self::TYPE_ALL, $anonymize = false)
    {
        $type = strtolower($type);
        $qb = $this->createQueryBuilder('e');
        if ($anonymize) {
            $qb->update()
                ->set('e.organizer', ':new_value')
                ->setParameter('new_value', null);
        } else {
            $qb->delete()
                ->set('e.organizer', $qb->expr()->literal(null));
        }

        $qb->where('e.organizer = :organizer')
            ->setParameter('organizer', $organizer);

        if (in_array($type, [self::TYPE_UPCOMING, self::TYPE_PAST], true)) {
            if (self::TYPE_PAST === $type) {
                $qb->andWhere('e.beginAt <= :date');
            } else {
                $qb->andWhere('e.beginAt >= :date');
            }
            // The extra 24 hours enable to include events in foreign
            // countries that are on different timezones.
            $qb->setParameter('date', new \DateTime('-24 hours'));
        }

        return $qb->getQuery()->execute();
    }
}
