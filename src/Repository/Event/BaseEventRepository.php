<?php

namespace App\Repository\Event;

use ApiPlatform\Core\DataProvider\PaginatorInterface;
use App\Entity\Adherent;
use App\Entity\Event\BaseEvent;
use App\Entity\Event\CommitteeEvent;
use App\Entity\Event\DefaultEvent;
use App\Entity\Event\EventRegistration;
use App\Entity\Event\InstitutionalEvent;
use App\Entity\Event\MunicipalEvent;
use App\Entity\Geo\Zone;
use App\Repository\GeoZoneTrait;
use App\Repository\PaginatorTrait;
use App\Repository\UuidEntityRepositoryTrait;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\Query\Expr\Join;
use Doctrine\ORM\Query\Expr\Orx;
use Doctrine\ORM\QueryBuilder;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Component\Security\Core\User\UserInterface;

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

    public function findOneActiveBySlug(string $slug): ?BaseEvent
    {
        return $this
            ->createSlugQueryBuilder($slug)
            ->andWhere('event.status IN (:statuses)')
            ->setParameter('statuses', CommitteeEvent::ACTIVE_STATUSES)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }

    public function findWithRegistrationByUuids(array $uuids, UserInterface $user): array
    {
        self::validUuids($uuids);

        return $this->createQueryBuilder('event')
            ->innerJoin(EventRegistration::class, 'registration', Join::WITH, 'registration.event = event')
            ->andWhere('registration.adherentUuid = :adherent_uuid')
            ->andWhere('event.uuid IN (:uuids)')
            ->setParameter('adherent_uuid', $user->getUuidAsString())
            ->setParameter('uuids', $uuids)
            ->getQuery()
            ->getResult()
        ;
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

    public function findEventsToRemind(
        \DateTimeInterface $startAfter,
        \DateTimeInterface $startBefore,
        string $mode = null
    ): array {
        $qb = $this
            ->createQueryBuilder('event')
            ->andWhere((new Orx())
                ->add(sprintf('event INSTANCE OF %s', DefaultEvent::class))
                ->add(sprintf('event INSTANCE OF %s', CommitteeEvent::class))
                ->add(sprintf('event INSTANCE OF %s', InstitutionalEvent::class))
                ->add(sprintf('event INSTANCE OF %s', MunicipalEvent::class))
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
}
