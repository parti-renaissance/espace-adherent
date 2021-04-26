<?php

namespace App\Repository\Event;

use ApiPlatform\Core\DataProvider\PaginatorInterface;
use App\Entity\Adherent;
use App\Entity\Event\CommitteeEvent;
use App\Entity\Event\DefaultEvent;
use App\Entity\Geo\Zone;
use App\Repository\AbstractAdherentTokenRepository;
use App\Repository\GeoZoneTrait;
use App\Repository\PaginatorTrait;
use Doctrine\ORM\QueryBuilder;
use Doctrine\Persistence\ManagerRegistry;

class DefaultEventRepository extends AbstractAdherentTokenRepository
{
    use GeoZoneTrait;
    use PaginatorTrait;

    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, DefaultEvent::class);
    }

    /**
     * @param Zone[] $zones
     *
     * @return DefaultEvent[]|PaginatorInterface
     */
    public function findManagedByPaginator(array $zones, int $page = 1, int $limit = 50): PaginatorInterface
    {
        $qb = $this->createQueryBuilder('event')
            ->select('event', 'category', 'organizer')
            ->leftJoin('event.category', 'category')
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
        int $limit = 50,
        string $groupCategorySlug = null
    ): PaginatorInterface {
        $qb = $this
            ->createQueryBuilder('event')
            ->andWhere('event.organizer = :organizer')
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
}
