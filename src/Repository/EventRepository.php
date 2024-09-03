<?php

namespace App\Repository;

use ApiPlatform\State\Pagination\PaginatorInterface;
use App\Entity\Adherent;
use App\Entity\Committee;
use App\Entity\Event\BaseEvent;
use App\Entity\Event\BaseEventCategory;
use App\Entity\Event\CommitteeEvent;
use App\Event\EventTypeEnum;
use App\Event\EventVisibilityEnum;
use App\Geocoder\Coordinates;
use App\Search\SearchParametersFilter;
use Cake\Chronos\Chronos;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\Query;
use Doctrine\ORM\Query\Expr\Orx;
use Doctrine\ORM\Query\ResultSetMapping;
use Doctrine\ORM\QueryBuilder;
use Doctrine\ORM\Tools\Pagination\Paginator;
use Doctrine\Persistence\ManagerRegistry;

class EventRepository extends ServiceEntityRepository
{
    use PaginatorTrait;
    use GeoZoneTrait;
    use NearbyTrait;
    use UuidEntityRepositoryTrait {
        findOneByUuid as findOneByValidUuid;
    }

    public const TYPE_PAST = 'past';
    public const TYPE_UPCOMING = 'upcoming';
    public const TYPE_ALL = 'all';

    public function __construct(ManagerRegistry $registry, string $className = CommitteeEvent::class)
    {
        parent::__construct($registry, $className);
    }

    public function countElements(
        bool $onlyPublished = true,
        bool $withPrivate = false,
        bool $forRenaissance = false,
    ): int {
        $qb = $this
            ->createQueryBuilder('e')
            ->leftJoin('e.category', 'ec')
            ->select('COUNT(e)')
        ;

        if ($onlyPublished) {
            $qb->where('e.published = :published')
                ->andWhere('ec.status = :enabled')
                ->setParameter('published', true)
                ->setParameter('enabled', BaseEventCategory::ENABLED)
            ;
        }

        if (!$withPrivate) {
            $qb
                ->andWhere('e.visibility != :private_visibility')
                ->setParameter('private_visibility', EventVisibilityEnum::PRIVATE)
            ;
        }

        $qb
            ->andWhere('e.renaissanceEvent = :for_re')
            ->setParameter('for_re', $forRenaissance)
        ;

        return (int) $qb->getQuery()
            ->getSingleScalarResult()
        ;
    }

    public function findOneBySlug(string $slug): ?CommitteeEvent
    {
        $query = $this
            ->createQueryBuilder('e')
            ->select('e', 'a', 'c', 'o')
            ->leftJoin('e.category', 'a')
            ->leftJoin('e.committee', 'c')
            ->leftJoin('e.author', 'o')
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
            ->andWhere('e.status = :status')
            ->setParameter('status', BaseEvent::STATUS_SCHEDULED)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }

    public function findStartedEventBetweenDatesForZones(
        \DateTimeInterface $startDate,
        \DateTimeInterface $endDate,
        array $zones,
    ): array {
        if (!$zones) {
            return [];
        }

        $qb = $this
            ->createQueryBuilder('event')
            ->addSelect('adherent')
            ->join('event.author', 'adherent')
            ->where('event.beginAt < :end_date AND event.finishAt > :start_date')
            ->andWhere('event.status = :status')
            ->setParameters([
                'start_date' => $startDate,
                'end_date' => $endDate,
                'status' => BaseEvent::STATUS_SCHEDULED,
            ])
            ->leftJoin('event.committee', 'committee')
            ->leftJoin('committee.zones', 'committeeZones')
            ->leftJoin('adherent.zones', 'adherentZones')
        ;

        $adherentZonesCondition = $this->createGeoZonesQueryBuilder(
            $zones,
            $qb,
            Adherent::class,
            'adherent_2',
            'zones',
            'adherent_zone_2',
            null,
            true,
            'adherent_zone_parent'
        );

        $committeeZoneCondition = $this->createGeoZonesQueryBuilder(
            $zones,
            $qb,
            Committee::class,
            'committee_2',
            'zones',
            'committee_zone_2',
            null,
            true,
            'committee_zone_parent'
        );

        return $qb
            ->andWhere((new Orx())
                ->add(\sprintf('committee IS NOT NULL AND committee.id IN (%s)', $committeeZoneCondition->getDQL()))
                ->add(\sprintf('committee IS NULL AND adherent.id IN (%s)', $adherentZonesCondition->getDQL()))
            )
            ->getQuery()
            ->getResult()
        ;
    }

    protected function createSlugQueryBuilder(string $slug): QueryBuilder
    {
        return $this
            ->createQueryBuilder('e')
            ->select('e', 'a', 'c', 'o')
            ->leftJoin('e.category', 'a')
            ->leftJoin('e.committee', 'c')
            ->leftJoin('e.author', 'o')
            ->where('e.slug = :slug')
            ->setParameter('slug', $slug)
            ->andWhere('e.published = :published')
            ->setParameter('published', true)
        ;
    }

    public function countUpcomingEvents(bool $withPrivate = false): int
    {
        $qb = $this
            ->createUpcomingEventsQueryBuilder($withPrivate)
            ->select('COUNT(e.id)')
        ;

        return (int) $qb->getQuery()->getSingleScalarResult();
    }

    public function findEventsByOrganizerPaginator(
        Adherent $organizer,
        int $page = 1,
        int $limit = 50,
    ): PaginatorInterface {
        return $this->configurePaginator(
            $this
                ->createQueryBuilder('e')
                ->andWhere('e.author = :organizer')
                ->setParameter('organizer', $organizer)
                ->orderBy('e.createdAt', 'DESC'),
            $page,
            $limit,
            static function (Query $query) {
                $query
                    ->useResultCache(true)
                    ->setResultCacheLifetime(1800)
                ;
            }
        );
    }

    public function findAllPublished(int $page = 1, int $limit = 50): PaginatorInterface
    {
        $qb = $this->createQueryBuilder('event')
            ->select('event', 'organizer')
            ->leftJoin('event.author', 'organizer')
            ->where('event.published = :published')
            ->orderBy('event.beginAt', 'DESC')
            ->addOrderBy('event.name', 'ASC')
            ->setParameter('published', true)
        ;

        return $this->configurePaginator($qb, $page, $limit);
    }

    private function createUpcomingEventsQueryBuilder(
        bool $withPrivate = false,
        bool $forRenaissance = false,
    ): QueryBuilder {
        $qb = $this->createQueryBuilder('e')->select('e', 'ec', 'c', 'o');
        $qb->leftJoin('e.category', 'ec')
            ->leftJoin('e.committee', 'c')
            ->leftJoin('e.author', 'o')
            ->where('e.published = :published')
            ->andWhere('e.renaissanceEvent = :for_re')
            ->andWhere('e.status = :event_status')
            ->andWhere('e.beginAt >= :today')
            ->andWhere('ec.status = :category_status')
            ->orderBy('e.beginAt', 'ASC')
            ->setParameter('published', true)
            ->setParameter('event_status', BaseEvent::STATUS_SCHEDULED)
            ->setParameter('for_re', $forRenaissance)
            ->setParameter('today', (new Chronos('now'))->format('Y-m-d'))
            ->setParameter('category_status', BaseEventCategory::ENABLED)
        ;

        if (!$withPrivate) {
            $qb
                ->andWhere('e.visibility != :private_visibility')
                ->setParameter('private_visibility', EventVisibilityEnum::PRIVATE)
            ;
        }

        return $qb;
    }

    public function countSitemapEvents(): int
    {
        return (int) $this
            ->createSitemapQueryBuilder()
            ->select('COUNT(c) AS nb')
            ->getQuery()
            ->getSingleScalarResult()
        ;
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
            ->getArrayResult()
        ;
    }

    private function createSitemapQueryBuilder(): QueryBuilder
    {
        return $this
            ->createQueryBuilder('e')
            ->select('e', 'a', 'c', 'o')
            ->leftJoin('e.category', 'a')
            ->leftJoin('e.committee', 'c')
            ->leftJoin('e.author', 'o')
            ->where('c.status = :status')
            ->andWhere('e.published = :published')
            ->setParameter('status', Committee::APPROVED)
            ->setParameter('published', true)
        ;
    }

    public function searchAllEvents(SearchParametersFilter $search): array
    {
        $sql = <<<'SQL'
            SELECT events.uuid AS event_uuid, events.author_id AS event_organizer_id, events.committee_id AS event_committee_id,
            events.name AS event_name, events.category_id AS event_category_id, events.description AS event_description,
            events.begin_at AS event_begin_at, events.finish_at AS event_finish_at,
            events.capacity AS event_capacity, events.is_for_legislatives AS event_is_for_legislatives,
            events.created_at AS event_created_at, events.participants_count AS event_participants_count, events.slug AS event_slug,
            events.type AS event_type, events.address_address AS event_address_address,
            events.address_country AS event_address_country, events.address_city_name AS event_address_city_name,
            events.address_city_insee AS event_address_city_insee, events.address_postal_code AS event_address_postal_code,
            events.address_latitude AS event_address_latitude, events.address_longitude AS event_address_longitude, events.time_zone AS timeZone,
            event_category.name AS event_category_name,
            committees.uuid AS committee_uuid, committees.name AS committee_name, committees.slug AS committee_slug,
            committees.description AS committee_description, committees.created_by AS committee_created_by,
            committees.address_address AS committee_address_address, committees.address_country AS committee_address_country,
            committees.address_city_name AS committee_address_city_name, committees.address_city_insee AS committee_address_city_insee,
            committees.address_postal_code AS committee_address_postal_code, committees.address_latitude AS committee_address_latitude,
            committees.address_longitude AS committee_address_longitude, adherents.uuid AS adherent_uuid,
            adherents.email_address AS adherent_email_address, adherents.password AS adherent_password, adherents.old_password AS adherent_old_password,
            adherents.gender AS adherent_gender, adherents.first_name AS adherent_first_name,
            adherents.last_name AS adherent_last_name, adherents.birthdate AS adherent_birthdate,
            adherents.address_address AS adherent_address_address, adherents.address_country AS adherent_address_country,
            adherents.address_city_name AS adherent_address_city_name, adherents.address_city_insee AS adherent_address_city_insee,
            adherents.address_postal_code AS adherent_address_postal_code, adherents.address_latitude AS adherent_address_latitude,
            adherents.address_longitude AS adherent_address_longitude, adherents.position AS adherent_position,
            (6371 * ACOS(COS(RADIANS(:latitude)) * COS(RADIANS(events.address_latitude)) * COS(RADIANS(events.address_longitude) - RADIANS(:longitude)) + SIN(RADIANS(:latitude)) * SIN(RADIANS(events.address_latitude)))) AS distance
            FROM events
            LEFT JOIN adherents ON adherents.id = events.author_id
            LEFT JOIN committees ON committees.id = events.committee_id
            LEFT JOIN events_categories AS event_category ON event_category.id = events.category_id AND events.type IN (:base_event_types)
            WHERE (events.address_latitude IS NOT NULL
                AND events.address_longitude IS NOT NULL
                AND (6371 * ACOS(COS(RADIANS(:latitude)) * COS(RADIANS(events.address_latitude)) * COS(RADIANS(events.address_longitude) - RADIANS(:longitude)) + SIN(RADIANS(:latitude)) * SIN(RADIANS(events.address_latitude)))) < :distance_max
                AND events.begin_at > :today
                AND events.published = :published
                AND events.status = :scheduled
                AND events.renaissance_event = :renaissance
                AND event_category.id IS NOT NULL
                )
                __filter_query__
                __filter_category__
                __filter_referent_events__
                __filter_private__
            ORDER BY events.begin_at ASC, distance ASC
            LIMIT :max_results
            OFFSET :first_result
            SQL;

        if (!empty($searchQuery = $search->getQuery())) {
            $filterQuery = 'AND events.name like :query';
        }

        if ($category = $search->getEventCategory()) {
            $filterCategory = 'AND events.category_id = :category';
        }

        if ($search->getReferentEvents()) {
            $filterReferentEvents = 'AND events.committee_id IS NULL';
        }

        if (!$search->getWithPrivate()) {
            $filterPrivate = 'AND events.visibility != :private_visibility';
        }

        $sql = preg_replace(
            ['/__filter_query__/', '/__filter_category__/', '/__filter_referent_events__/', '/__filter_private__/'],
            [$filterQuery ?? '', $filterCategory ?? '', $filterReferentEvents ?? '', $filterPrivate ?? ''],
            $sql
        );

        $rsm = new ResultSetMapping();
        $query = $this->getEntityManager()->createNativeQuery($sql, $rsm);

        if ($search->getCityCoordinates()) {
            $query->setParameter('distance_max', $search->getRadius());
            $query->setParameter('today', new Chronos('now - 1 hour'));
        }

        if (!$search->getWithPrivate()) {
            $query->setParameter('private_visibility', EventVisibilityEnum::PRIVATE);
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
        $query->setParameter('base_event_types', [EventTypeEnum::TYPE_COMMITTEE, EventTypeEnum::TYPE_DEFAULT]);
        $query->setParameter('first_result', $search->getOffset(), \PDO::PARAM_INT);
        $query->setParameter('max_results', $search->getMaxResults(), \PDO::PARAM_INT);
        $query->setParameter('renaissance', $search->isRenaissanceEvent(), \PDO::PARAM_INT);

        return $query->getResult('EventHydrator');
    }

    public function removeOrganizerEvents(Adherent $organizer, string $type = self::TYPE_ALL, $anonymize = false)
    {
        $type = strtolower($type);
        $qb = $this->createQueryBuilder('e');
        if ($anonymize) {
            $qb->update()
                ->set('e.author', ':new_value')
                ->setParameter('new_value', null)
            ;
        } else {
            $qb->delete();
        }

        $qb
            ->where('e.author = :organizer')
            ->setParameter('organizer', $organizer)
        ;

        if (\in_array($type, [self::TYPE_UPCOMING, self::TYPE_PAST], true)) {
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

    public function paginate(int $offset = 0, int $limit = SearchParametersFilter::DEFAULT_MAX_RESULTS): Paginator
    {
        $query = $this->createQueryBuilder('e')
            ->getQuery()
            ->setMaxResults($limit)
            ->setFirstResult($offset)
        ;

        return new Paginator($query);
    }

    /**
     * @return CommitteeEvent[]
     */
    public function findNearbyOf(
        CommitteeEvent $event,
        int $radius = SearchParametersFilter::RADIUS_10,
        int $max = 3,
    ): array {
        return $this
            ->createNearbyQueryBuilder(new Coordinates($event->getLatitude(), $event->getLongitude()))
            ->andWhere($this->getNearbyExpression('n').' < :distance_max')
            ->andWhere('n.beginAt > :date')
            ->andWhere('n.status = :status')
            ->andwhere('n.published = :published')
            ->andwhere('n.renaissanceEvent = :for_renaissance_event')
            ->setParameter('distance_max', $radius)
            ->setParameter('date', $event->getBeginAt())
            ->setParameter('status', BaseEvent::STATUS_SCHEDULED)
            ->setParameter('published', true)
            ->setParameter('for_renaissance_event', false)
            ->addOrderBy('n.beginAt', 'ASC')
            ->setMaxResults($max)
            ->getQuery()
            ->getResult()
        ;
    }
}
