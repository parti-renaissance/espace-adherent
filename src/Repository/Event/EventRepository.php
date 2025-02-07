<?php

namespace App\Repository\Event;

use ApiPlatform\State\Pagination\PaginatorInterface;
use App\Entity\Adherent;
use App\Entity\Committee;
use App\Entity\Event\BaseEventCategory;
use App\Entity\Event\Event;
use App\Entity\Geo\Zone;
use App\Event\EventVisibilityEnum;
use App\Event\ListFilter;
use App\Geocoder\Coordinates;
use App\Repository\GeoZoneTrait;
use App\Repository\NearbyTrait;
use App\Repository\PaginatorTrait;
use App\Repository\UuidEntityRepositoryTrait;
use App\Search\SearchParametersFilter;
use Cake\Chronos\Chronos;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\Query\Expr\Orx;
use Doctrine\ORM\Query\ResultSetMapping;
use Doctrine\ORM\QueryBuilder;
use Doctrine\ORM\Tools\Pagination\Paginator;
use Doctrine\Persistence\ManagerRegistry;

class EventRepository extends ServiceEntityRepository
{
    use UuidEntityRepositoryTrait;
    use PaginatorTrait;
    use GeoZoneTrait;
    use NearbyTrait;
    use UuidEntityRepositoryTrait {
        findOneByUuid as findOneByValidUuid;
    }

    public const TYPE_PAST = 'past';
    public const TYPE_UPCOMING = 'upcoming';
    public const TYPE_ALL = 'all';

    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Event::class);
    }

    public function findOnePublishedBySlug(string $slug): ?Event
    {
        return $this
            ->createSlugQueryBuilder($slug)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }

    /**
     * @param Zone[] $zones
     *
     * @return Event[]|PaginatorInterface
     */
    public function findManagedByPaginator(array $zones, int $page = 1, int $limit = 50): PaginatorInterface
    {
        $qb = $this->createQueryBuilder('event')
            ->select('event', 'category', 'organizer')
            ->leftJoin('event.category', 'category')
            ->leftJoin('event.author', 'organizer')
            ->where('event.published = :published')
            ->orderBy('event.beginAt', 'DESC')
            ->addOrderBy('event.name', 'ASC')
            ->setParameter('published', true)
        ;

        $this->withGeoZones(
            $zones,
            $qb,
            'event',
            Event::class,
            'e2',
            'zones',
            'z2',
            function (QueryBuilder $zoneQueryBuilder, string $entityClassAlias) {
                $zoneQueryBuilder->andWhere(\sprintf('%s.published = :published', $entityClassAlias));
            }
        );

        return $this->configurePaginator($qb, $page, $limit);
    }

    public function findOneActiveBySlug(string $slug): ?Event
    {
        return $this
            ->createSlugQueryBuilder($slug)
            ->andWhere('event.status = :status')
            ->setParameter('status', Event::STATUS_SCHEDULED)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }

    protected function createSlugQueryBuilder(string $slug): QueryBuilder
    {
        return $this
            ->createQueryBuilder('event')
            ->select('event', 'organizer')
            ->leftJoin('event.author', 'organizer')
            ->where('event.slug = :slug')
            ->andWhere('event.published = :published')
            ->setParameters([
                'published' => true,
                'slug' => $slug,
            ])
        ;
    }

    public function findEventsToRemind(
        \DateTimeInterface $startAfter,
        \DateTimeInterface $startBefore,
        ?string $mode = null,
    ): array {
        $qb = $this
            ->createQueryBuilder('event')
            ->andWhere('event.beginAt >= :start_after')
            ->andWhere('event.beginAt < :start_before')
            ->andWhere('event.reminded = :false')
            ->setParameters([
                'start_after' => $startAfter,
                'start_before' => $startBefore,
                'false' => false,
            ])
        ;

        if ($mode) {
            $qb
                ->andWhere('event.mode = :mode')
                ->setParameter('mode', $mode)
            ;
        }

        return $qb
            ->getQuery()
            ->getResult()
        ;
    }

    public function findAllByFilter(ListFilter $filter, int $limit = 50): array
    {
        $qb = $this->createQueryBuilder('event');

        $qb
            ->leftJoin('event.category', 'category')
            ->where('event.published = :published')
            ->andWhere('event.status = :event_status')
            ->andWhere('event.beginAt >= CONVERT_TZ(:now, \'Europe/Paris\', event.timeZone)')
            ->andWhere('category IS NULL OR category.status = :category_status')
            ->orderBy('event.beginAt', 'ASC')
            ->addOrderBy('event.name', 'ASC')
            ->setParameter('published', true)
            ->setParameter('event_status', Event::STATUS_SCHEDULED)
            ->setParameter('now', new \DateTime('now'))
            ->setParameter('category_status', BaseEventCategory::ENABLED)
            ->setMaxResults($limit)
        ;

        if ($name = $filter->getName()) {
            $qb
                ->andWhere('event.name LIKE :name')
                ->setParameter('name', '%'.$name.'%')
            ;
        }

        if ($category = $filter->getCategory()) {
            $qb
                ->andWhere('category = :category')
                ->setParameter('category', $category)
            ;
        }

        if ($zone = $filter->getZone() ?? $filter->getDefaultZone()) {
            $this->withGeoZones(
                array_merge([$zone], $zone->isCountry() ? $zone->getParentsOfType(Zone::CUSTOM) : []),
                $qb,
                'event',
                Event::class,
                'e2',
                'zones',
                'z2',
                function (QueryBuilder $zoneQueryBuilder, string $entityClassAlias) {
                    $zoneQueryBuilder->andWhere(\sprintf('%s.published = :published', $entityClassAlias));
                },
                !$zone->isCountry()
            );
        }

        return $qb->getQuery()->getResult();
    }

    public function findAllForPublicMap(?string $categorySlug): array
    {
        $qb = $this->createQueryBuilder('e')
            ->select(
                'e.uuid',
                'e.slug',
                'e.name',
                'e.beginAt',
                'e.postAddress.latitude AS latitude',
                'e.postAddress.longitude AS longitude',
                'e.postAddress.cityName AS city',
                'e.postAddress.postalCode AS postalCode',
                'e.postAddress.country AS country',
            )
            ->where('e.visibility IN (:visibilities)')
            ->andWhere('e.postAddress.latitude IS NOT NULL AND e.postAddress.longitude IS NOT NULL')
            ->andWhere('e.status = :status')
            ->andWhere('e.mode = :mode')
            ->setParameters([
                'mode' => Event::MODE_MEETING,
                'status' => Event::STATUS_SCHEDULED,
                'visibilities' => [EventVisibilityEnum::PUBLIC, EventVisibilityEnum::PRIVATE],
            ])
        ;

        if ($categorySlug) {
            $qb
                ->innerJoin('e.category', 'c')
                ->andWhere('c.slug = :category AND c.status = :cat_status')
                ->setParameter('category', $categorySlug)
                ->setParameter('cat_status', BaseEventCategory::ENABLED)
            ;
        }

        return $qb->getQuery()->enableResultCache(3600)->getArrayResult();
    }

    /**
     * @return Event[]|PaginatorInterface
     */
    public function findEventsByOrganizerPaginator(
        Adherent $organizer,
        int $page = 1,
        int $limit = 50,
        ?string $groupCategorySlug = null,
    ): PaginatorInterface {
        $qb = $this
            ->createQueryBuilder('event')
            ->andWhere('event.author = :organizer')
            ->setParameter('organizer', $organizer)
            ->orderBy('event.createdAt', 'DESC')
        ;

        if ($groupCategorySlug) {
            $qb
                ->innerJoin('event.category', 'category')
                ->innerJoin('category.eventGroupCategory', 'groupCategory')
                ->andWhere('groupCategory.slug = :group_category_slug')
                ->setParameter('group_category_slug', $groupCategorySlug)
            ;
        }

        return $this->configurePaginator($qb, $page, $limit);
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

    public function findOneBySlug(string $slug): ?Event
    {
        return $this->findOneBy(['slug' => $slug]);
    }

    public function findOneByUuid(string $uuid): ?Event
    {
        return $this->findOneByValidUuid($uuid);
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
                'status' => Event::STATUS_SCHEDULED,
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

    public function searchAllEvents(SearchParametersFilter $search): array
    {
        $sql = <<<'SQL'
            SELECT events.uuid AS event_uuid, events.author_id AS event_organizer_id, events.committee_id AS event_committee_id,
            events.name AS event_name, events.category_id AS event_category_id, events.description AS event_description,
            events.begin_at AS event_begin_at, events.finish_at AS event_finish_at,
            events.capacity AS event_capacity,
            events.created_at AS event_created_at, events.participants_count AS event_participants_count, events.slug AS event_slug,
            events.address_address AS event_address_address,
            events.address_country AS event_address_country, events.address_city_name AS event_address_city_name,
            events.address_city_insee AS event_address_city_insee, events.address_postal_code AS event_address_postal_code,
            events.address_latitude AS event_address_latitude, events.address_longitude AS event_address_longitude, events.time_zone AS timeZone,
            event_category.name AS event_category_name,
            committees.uuid AS committee_uuid, committees.name AS committee_name, committees.slug AS committee_slug,
            committees.description AS committee_description, committees.created_by AS committee_created_by,
            committees.address_address AS committee_address_address, committees.address_country AS committee_address_country,
            committees.address_city_name AS committee_address_city_name, committees.address_city_insee AS committee_address_city_insee,
            committees.address_postal_code AS committee_address_postal_code, committees.address_latitude AS committee_address_latitude,
            committees.address_longitude AS committee_address_longitude,
            adherents.uuid AS adherent_uuid,
            adherents.public_id AS adherent_public_id,
            adherents.email_address AS adherent_email_address,
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
            LEFT JOIN events_categories AS event_category ON event_category.id = events.category_id
            WHERE (events.address_latitude IS NOT NULL
                AND events.address_longitude IS NOT NULL
                AND (6371 * ACOS(COS(RADIANS(:latitude)) * COS(RADIANS(events.address_latitude)) * COS(RADIANS(events.address_longitude) - RADIANS(:longitude)) + SIN(RADIANS(:latitude)) * SIN(RADIANS(events.address_latitude)))) < :distance_max
                AND events.begin_at > :today
                AND events.published = :published
                AND events.status = :scheduled
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
        $query->setParameter('scheduled', Event::STATUS_SCHEDULED);
        $query->setParameter('first_result', $search->getOffset(), \PDO::PARAM_INT);
        $query->setParameter('max_results', $search->getMaxResults(), \PDO::PARAM_INT);

        return $query->getResult('EventHydrator');
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
     * @return Event[]
     */
    public function findNearbyOf(
        Event $event,
        int $radius = SearchParametersFilter::RADIUS_10,
        int $max = 3,
    ): array {
        return $this
            ->createNearbyQueryBuilder(new Coordinates($event->getLatitude(), $event->getLongitude()))
            ->andWhere($this->getNearbyExpression('n').' < :distance_max')
            ->andWhere('n.beginAt > :date')
            ->andWhere('n.status = :status')
            ->andwhere('n.published = :published')
            ->setParameter('distance_max', $radius)
            ->setParameter('date', $event->getBeginAt())
            ->setParameter('status', Event::STATUS_SCHEDULED)
            ->setParameter('published', true)
            ->addOrderBy('n.beginAt', 'ASC')
            ->setMaxResults($max)
            ->getQuery()
            ->getResult()
        ;
    }

    /**
     * @return Event[]
     */
    public function findWithLiveStream(): array
    {
        return $this->createQueryBuilder('e')
            ->addSelect('IF(e.beginAt < :now, 2, 1) AS HIDDEN priority')
            ->addSelect('ABS(TIMESTAMPDIFF(SECOND, NOW(), e.beginAt)) AS HIDDEN time_to_begin')
            ->addOrderBy('priority', 'DESC')
            ->addOrderBy('time_to_begin', 'ASC')
            ->where('e.status = :status')
            ->andWhere('e.national = 1')
            ->andWhere('e.liveUrl LIKE :live_url')
            ->andWhere('DATE_SUB(e.beginAt, 2, \'DAY\') < :now')
            ->andWhere('e.finishAt >= :now')
            ->setParameters([
                'status' => Event::STATUS_SCHEDULED,
                'live_url' => 'https://vimeo.com/%',
                'now' => new \DateTime('now'),
            ])
            ->getQuery()
            ->getResult()
        ;
    }
}
