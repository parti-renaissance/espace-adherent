<?php

namespace AppBundle\Repository;

use AppBundle\Entity\Adherent;
use AppBundle\Entity\Committee;
use AppBundle\Entity\Event;
use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\QueryBuilder;

class EventRepository extends EntityRepository
{
    use NearbyTrait;

    public function count(): int
    {
        return (int) $this
            ->createQueryBuilder('e')
            ->select('COUNT(e)')
            ->getQuery()
            ->getSingleScalarResult();
    }

    public function findOneBySlug(string $slug): ?Event
    {
        $query = $this
            ->createQueryBuilder('e')
            ->select('e, c, o')
            ->leftJoin('e.committee', 'c')
            ->leftJoin('e.organizer', 'o')
            ->where('e.slug = :slug')
            ->andWhere('c.status = :status')
            ->setParameter('slug', $slug)
            ->setParameter('status', Committee::APPROVED)
            ->getQuery()
        ;

        return $query->getOneOrNullResult();
    }

    public function findMostRecentEvent(): ?Event
    {
        $query = $this
            ->createQueryBuilder('ce')
            ->orderBy('ce.createdAt', 'DESC')
            ->setMaxResults(1)
            ->getQuery();

        return $query->getOneOrNullResult();
    }

    public function findOneByUuid(string $uuid): ?Event
    {
        return $this->findOneBy(['uuid' => $uuid]);
    }

    public function findOneActiveByUuid(string $uuid): ?Event
    {
        $query = $this->createQueryBuilder('e')
            ->where('e.uuid = :uuid')
            ->andWhere('e.status IN (:statuses)')
            ->setParameter('uuid', $uuid)
            ->setParameter('statuses', Event::ACTIVE_STATUSES)
            ->getQuery();

        return $query->getOneOrNullResult();
    }

    public function findManagedBy(Adherent $referent)
    {
        $qb = $this->createQueryBuilder('e')
            ->select('e')
            ->orderBy('e.beginAt', 'DESC')
            ->addOrderBy('e.name', 'ASC');

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
     * @return int
     */
    public function countSitemapEvents(): int
    {
        return (int) $this->createSitemapQb()
            ->select('COUNT(c) AS nb')
            ->getQuery()
            ->getSingleScalarResult();
    }

    /**
     * @param int $page
     * @param int $perPage
     *
     * @return array
     */
    public function findSitemapEvents(int $page, int $perPage): array
    {
        return $this->createSitemapQb()
            ->select('e.uuid', 'e.slug', 'e.updatedAt')
            ->orderBy('e.id')
            ->setFirstResult(($page - 1) * $perPage)
            ->setMaxResults($perPage)
            ->getQuery()
            ->getArrayResult();
    }

    /**
     * @return QueryBuilder
     */
    private function createSitemapQb(): QueryBuilder
    {
        return $this
            ->createQueryBuilder('e')
            ->leftJoin('e.committee', 'c')
            ->where('c.status = :status')
            ->setParameter('status', Committee::APPROVED);
    }
}
