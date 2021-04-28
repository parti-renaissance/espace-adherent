<?php

namespace App\Repository\Event;

use ApiPlatform\Core\DataProvider\PaginatorInterface;
use App\Entity\Adherent;
use App\Entity\Event\BaseEvent;
use App\Entity\Event\CommitteeEvent;
use App\Entity\Event\DefaultEvent;
use App\Entity\Geo\Zone;
use App\Repository\AbstractAdherentTokenRepository;
use App\Repository\GeoZoneTrait;
use App\Repository\PaginatorTrait;
use Doctrine\ORM\QueryBuilder;
use Doctrine\Persistence\ManagerRegistry;

class BaseEventRepository extends AbstractAdherentTokenRepository
{
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
            ->leftJoin('event.organizer', 'organizer')
            ->where('event.published = :published')
            ->orderBy('event.beginAt', 'DESC')
            ->addOrderBy('event.name', 'ASC')
            ->setParameter('published', true)
        ;

        $this->withGeoZones(
            $zones,
            $qb,
            'event',
            CommitteeEvent::class,
            'e2',
            'zones',
            'z2',
            function (QueryBuilder $zoneQueryBuilder, string $entityClassAlias) {
                $zoneQueryBuilder->andWhere(sprintf('%s.published = :published', $entityClassAlias));
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
        int $limit = 50
    ): PaginatorInterface {
        $qb = $this
            ->createQueryBuilder('event')
            ->andWhere('event.organizer = :organizer')
            ->setParameter('organizer', $organizer)
            ->orderBy('event.createdAt', 'DESC')
        ;

        return $this->configurePaginator($qb, $page, $limit);
    }

    protected function createSlugQueryBuilder(string $slug): QueryBuilder
    {
        return $this
            ->createQueryBuilder('event')
            ->select('event', 'organizer')
            ->leftJoin('event.organizer', 'organizer')
            ->where('event.slug = :slug')
            ->andWhere('event.published = :published')
            ->setParameters([
                'published' => true,
                'slug' => $slug,
            ])
        ;
    }
}
