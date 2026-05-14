<?php

declare(strict_types=1);

namespace App\Repository\Event;

use App\Adherent\Tag\TagEnum;
use App\Entity\Adherent;
use App\Entity\Agora;
use App\Entity\Committee;
use App\Entity\Event\BaseEventCategory;
use App\Entity\Event\Event;
use App\Entity\Event\EventRegistration;
use App\Entity\Geo\Zone;
use App\Event\EventVisibilityEnum;
use App\Event\ListFilter;
use App\Geocoder\Coordinates;
use App\Repository\GeoZoneTrait;
use App\Repository\NearbyTrait;
use App\Repository\UuidEntityRepositoryTrait;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Query\Parameter;
use Doctrine\ORM\QueryBuilder;
use Doctrine\ORM\Tools\Pagination\Paginator;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Component\Uid\Uuid;

class EventRepository extends ServiceEntityRepository
{
    use UuidEntityRepositoryTrait;
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
            ->setParameters(new ArrayCollection([
                new Parameter('published', true),
                new Parameter('slug', $slug),
            ]))
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
            ->setParameters(new ArrayCollection([
                new Parameter('start_after', $startAfter),
                new Parameter('start_before', $startBefore),
                new Parameter('false', false),
            ]))
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

    /** @return Event[] */
    public function findEventsToRemindByEmail(
        \DateTimeInterface $startAfter,
        \DateTimeInterface $startBefore,
    ): array {
        return $this->createQueryBuilder('event')
            ->where('event.published = :published')
            ->andWhere('event.status = :event_status')
            ->andWhere('event.beginAt >= :start_after')
            ->andWhere('event.beginAt < :start_before')
            ->andWhere('event.emailReminded = :email_reminded')
            ->andWhere('event.hidden = :hidden')
            ->setParameters(new ArrayCollection([
                new Parameter('published', true),
                new Parameter('event_status', Event::STATUS_SCHEDULED),
                new Parameter('start_after', $startAfter),
                new Parameter('start_before', $startBefore),
                new Parameter('email_reminded', false),
                new Parameter('hidden', false),
            ]))
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
            ->andWhere('event.hidden = :hidden')
            ->andWhere('event.beginAt >= CONVERT_TZ(:now, \'Europe/Paris\', event.timeZone)')
            ->andWhere('category IS NULL OR category.status = :category_status')
            ->orderBy('event.beginAt', 'ASC')
            ->addOrderBy('event.name', 'ASC')
            ->setParameter('published', true)
            ->setParameter('event_status', Event::STATUS_SCHEDULED)
            ->setParameter('hidden', false)
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
            ->andWhere('e.hidden = :hidden')
            ->setParameters(new ArrayCollection([
                new Parameter('mode', Event::MODE_MEETING),
                new Parameter('status', Event::STATUS_SCHEDULED),
                new Parameter('visibilities', [EventVisibilityEnum::PUBLIC, EventVisibilityEnum::PRIVATE]),
                new Parameter('hidden', false),
            ]))
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

    public function findOneByUuid(Uuid|string $uuid): ?Event
    {
        return $this->findOneByValidUuid($uuid);
    }

    public function paginate(int $offset = 0, int $limit = 30): Paginator
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
        int $radius = 10,
        int $max = 3,
    ): array {
        return $this
            ->createNearbyQueryBuilder(new Coordinates($event->getLatitude(), $event->getLongitude()))
            ->andWhere($this->getNearbyExpression('n').' < :distance_max')
            ->andWhere('n.beginAt > :date')
            ->andWhere('n.status = :status')
            ->andwhere('n.published = :published')
            ->andWhere('n.hidden = :hidden')
            ->setParameter('distance_max', $radius)
            ->setParameter('date', $event->getBeginAt())
            ->setParameter('status', Event::STATUS_SCHEDULED)
            ->setParameter('published', true)
            ->setParameter('hidden', false)
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
            ->andWhere('DATE(e.beginAt) = :today AND e.finishAt >= :now')
            ->setParameters(new ArrayCollection([
                new Parameter('status', Event::STATUS_SCHEDULED),
                new Parameter('live_url', 'https://vimeo.com/%'),
                new Parameter('now', $now = new \DateTime('now')),
                new Parameter('today', $now->format('Y-m-d')),
            ]))
            ->getQuery()
            ->getResult()
        ;
    }

    /**
     * @return Event[]
     */
    public function findWithLiveToNotify(): array
    {
        return $this->createQueryBuilder('e')
            ->where('e.status = :status')
            ->andWhere('e.national = 1')
            ->andWhere('e.liveUrl LIKE :live_url')
            ->andWhere('e.pushSentAt IS NULL')
            ->andWhere('e.beginAt < :now AND e.finishAt >= :now')
            ->setParameters(new ArrayCollection([
                new Parameter('status', Event::STATUS_SCHEDULED),
                new Parameter('live_url', 'https://vimeo.com/%'),
                new Parameter('now', new \DateTime('now')),
            ]))
            ->getQuery()
            ->getResult()
        ;
    }

    /**
     * @return Event[]
     */
    public function findAllFutureInvitationEvents(Agora|Committee $container, \DateTime $from): array
    {
        return $this->createFutureInvitationEventsQueryBuilder($container, $from)
            ->getQuery()
            ->getResult()
        ;
    }

    /**
     * @return Event[]
     */
    public function findAllFutureInvitationEventsWithoutAdherent(Agora|Committee $container, Adherent $adherent, \DateTime $from): array
    {
        return $this->createFutureInvitationEventsQueryBuilder($container, $from)
            ->leftJoin(EventRegistration::class, 'er', 'WITH', 'er.event = e AND er.adherent = :adherent')
            ->andWhere('er IS NULL')
            ->setParameter('adherent', $adherent)
            ->getQuery()
            ->getResult()
        ;
    }

    private function createFutureInvitationEventsQueryBuilder(Agora|Committee $container, \DateTime $from): QueryBuilder
    {
        $field = $container instanceof Agora ? 'agora' : 'committee';

        return $this->createQueryBuilder('e')
            ->select('e')
            ->where("e.$field = :container")
            ->andWhere('e.visibility = :visibility')
            ->andWhere('e.beginAt >= :from')
            ->andWhere('e.published = :published')
            ->andWhere('e.status = :status')
            ->andWhere('e.hidden = :hidden')
            ->setParameter('container', $container)
            ->setParameter('visibility', EventVisibilityEnum::INVITATION)
            ->setParameter('from', $from)
            ->setParameter('published', true)
            ->setParameter('status', Event::STATUS_SCHEDULED)
            ->setParameter('hidden', false)
        ;
    }

    public function updateRegistrationsCounters(): void
    {
        $this->getEntityManager()->getConnection()->executeQuery(
            'UPDATE events AS e
            INNER JOIN (
                SELECT
                    e2.id,
                    COUNT(DISTINCT er.id) AS participants_count,
                    SUM(IF(a.tags LIKE ?, 1, 0)) AS adherents_up_to_date_count,
                    SUM(IF(a.tags LIKE ?, 1, 0)) AS adherents_not_up_to_date_count,
                    SUM(IF(a.tags LIKE ?, 1, 0)) AS sympathizers_count,
                    SUM(IF(a.tags LIKE ?, 1, 0)) AS members_em_count,
                    SUM(IF(a.id IS NULL, 1, 0)) AS citizens_count
                FROM events e2
                INNER JOIN events_registrations er ON er.event_id = e2.id
                LEFT JOIN adherents a ON a.id = er.adherent_id
                GROUP BY e2.id
            ) AS t ON t.id = e.id
            SET
                e.participants_count = t.participants_count,
                e.adherents_up_to_date_count = t.adherents_up_to_date_count,
                e.adherents_not_up_to_date_count = t.adherents_not_up_to_date_count,
                e.members_em_count = t.members_em_count,
                e.sympathizers_count = t.sympathizers_count,
                e.citizens_count = t.citizens_count',
            [
                TagEnum::getAdherentYearTag().'%',
                TagEnum::ADHERENT_NOT_UP_TO_DATE.'%',
                TagEnum::SYMPATHISANT.'%',
                TagEnum::SYMPATHISANT_COMPTE_EM.'%',
            ]
        );
    }
}
