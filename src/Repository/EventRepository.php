<?php

namespace AppBundle\Repository;

use AppBundle\Entity\Adherent;
use AppBundle\Entity\Committee;
use AppBundle\Entity\Event;
use AppBundle\Search\SearchParametersFilter;
use Doctrine\ORM\EntityRepository;
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

    /**
     * @throws \InvalidArgumentException
     */
    public function findOneActiveByUuid(string $uuid): ?Event
    {
        self::validUuid($uuid);

        $query = $this->createQueryBuilder('e')
            ->select('e', 'a', 'c', 'o')
            ->leftJoin('e.category', 'a')
            ->leftJoin('e.committee', 'c')
            ->leftJoin('e.organizer', 'o')
            ->where('e.uuid = :uuid')
            ->andWhere('e.status IN (:statuses)')
            ->andWhere('e.published = :published')
            ->setParameter('uuid', $uuid)
            ->setParameter('statuses', Event::ACTIVE_STATUSES)
            ->setParameter('published', true)
            ->getQuery();

        return $query->getOneOrNullResult();
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

        $qb->andWhere('e.published = :published')
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
}
