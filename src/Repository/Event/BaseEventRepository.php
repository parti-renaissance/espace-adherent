<?php

namespace App\Repository\Event;

use ApiPlatform\State\Pagination\PaginatorInterface;
use App\Entity\Adherent;
use App\Entity\Event\BaseEvent;
use App\Entity\Event\BaseEventCategory;
use App\Entity\Event\CommitteeEvent;
use App\Entity\Event\DefaultEvent;
use App\Entity\Geo\Zone;
use App\Event\ListFilter;
use App\Repository\GeoZoneTrait;
use App\Repository\PaginatorTrait;
use App\Repository\UuidEntityRepositoryTrait;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\Query\Expr\Orx;
use Doctrine\ORM\QueryBuilder;
use Doctrine\Persistence\ManagerRegistry;

class BaseEventRepository extends ServiceEntityRepository
{
    use UuidEntityRepositoryTrait;
    use PaginatorTrait;
    use GeoZoneTrait;

    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, BaseEvent::class);
    }

    public function findOnePublishedBySlug(string $slug): ?BaseEvent
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
     * @return BaseEvent[]|PaginatorInterface
     */
    public function findManagedByPaginator(array $zones, int $page = 1, int $limit = 50): PaginatorInterface
    {
        $qb = $this->createQueryBuilder('event')
            ->select('event', 'organizer')
            ->leftJoin('event.author', 'organizer')
            ->where('event.published = :published')
            ->andWhere('event.renaissanceEvent = :re_event')
            ->orderBy('event.beginAt', 'DESC')
            ->addOrderBy('event.name', 'ASC')
            ->setParameter('published', true)
            ->setParameter('re_event', false)
        ;

        $this->withGeoZones(
            $zones,
            $qb,
            'event',
            BaseEvent::class,
            'e2',
            'zones',
            'z2',
            function (QueryBuilder $zoneQueryBuilder, string $entityClassAlias) {
                $zoneQueryBuilder->andWhere(\sprintf('%s.published = :published', $entityClassAlias));
            }
        );

        return $this->configurePaginator($qb, $page, $limit);
    }

    /**
     * @return DefaultEvent[]|PaginatorInterface
     */
    public function findEventsByOrganizerPaginator(
        Adherent $organizer,
        int $page = 1,
        int $limit = 50,
    ): PaginatorInterface {
        $qb = $this
            ->createQueryBuilder('event')
            ->andWhere('event.author = :organizer')
            ->setParameter('organizer', $organizer)
            ->orderBy('event.createdAt', 'DESC')
        ;

        return $this->configurePaginator($qb, $page, $limit);
    }

    public function findOneActiveBySlug(string $slug): ?BaseEvent
    {
        return $this
            ->createSlugQueryBuilder($slug)
            ->andWhere('event.status = :status')
            ->setParameter('status', BaseEvent::STATUS_SCHEDULED)
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
            ->andWhere((new Orx())
                ->add(\sprintf('event INSTANCE OF %s', DefaultEvent::class))
                ->add(\sprintf('event INSTANCE OF %s', CommitteeEvent::class))
            )
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
            ->andWhere('event.renaissanceEvent = :re_event')
            ->andWhere((new Orx())
                ->add(\sprintf('event INSTANCE OF %s', DefaultEvent::class))
                ->add(\sprintf('event INSTANCE OF %s', CommitteeEvent::class))
            )
            ->andWhere('event.status = :event_status')
            ->andWhere('event.beginAt >= CONVERT_TZ(:now, \'Europe/Paris\', event.timeZone)')
            ->andWhere('category IS NULL OR category.status = :category_status')
            ->orderBy('event.beginAt', 'ASC')
            ->addOrderBy('event.name', 'ASC')
            ->setParameter('published', true)
            ->setParameter('re_event', true)
            ->setParameter('event_status', BaseEvent::STATUS_SCHEDULED)
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
                BaseEvent::class,
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
}
