<?php

namespace AppBundle\Repository;

use AppBundle\Entity\Committee;
use AppBundle\Geocoder\Coordinates;
use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\QueryBuilder;
use Ramsey\Uuid\Uuid;

class CommitteeRepository extends EntityRepository
{
    const EARTH_MEAN_RADIUS = '6371';

    const ONLY_APPROVED = 1;
    const INCLUDE_UNAPPROVED = 2;

    /**
     * Finds a Committee instance by its unique canonical name.
     *
     * @param string $name
     *
     * @return Committee|null
     */
    public function findOneByName(string $name): ?Committee
    {
        $canonicalName = Committee::canonicalize($name);

        return $this->findOneBy(['canonicalName' => $canonicalName]);
    }

    /**
     * Finds a Committee instance by its unique UUID.
     *
     * @param string $uuid
     *
     * @return Committee|null
     */
    public function findOneByUuid(string $uuid): ?Committee
    {
        return $this->findOneBy(['uuid' => $uuid]);
    }

    /**
     * Finds Committee instances by their unique UUIDs.
     *
     * @param string[] $uuids
     *
     * @return Committee[]
     */
    public function findByUuid(array $uuids)
    {
        return $this->findBy(['uuid' => $uuids]);
    }

    /**
     * Finds approved Committee instances.
     *
     * @return Committee[]
     */
    public function findApprovedCommittees()
    {
        return $this->findBy(['status' => Committee::APPROVED]);
    }

    /**
     * Returns whether or not the given adherent has "waiting for approval"
     * committees.
     *
     * @param string $adherentUuid
     *
     * @return bool
     */
    public function hasWaitingForApprovalCommittees(string $adherentUuid): bool
    {
        $adherentUuid = Uuid::fromString($adherentUuid);

        $query = $this
            ->createQueryBuilder('c')
            ->select('COUNT(c.uuid)')
            ->where('c.createdBy = :adherent')
            ->andWhere('c.status = :status')
            ->setParameter('adherent', (string) $adherentUuid)
            ->setParameter('status', Committee::PENDING)
            ->getQuery()
        ;

        return (int) $query->getSingleScalarResult() >= 1;
    }

    /**
     * Returns the most recent created Committee.
     *
     * @return Committee|null
     */
    public function findMostRecentCommittee(): ?Committee
    {
        $query = $this
            ->createQueryBuilder('c')
            ->orderBy('c.createdAt', 'DESC')
            ->setMaxResults(1)
            ->getQuery()
        ;

        return $query->getOneOrNullResult();
    }

    /**
     * Calculates the distance (in Km) between the committee and the provided geographical
     * points in a select statement. You can use this template to apply your constraints
     * by using the 'distance_between' attribute.
     *
     * Setting the hidden flag to false allow you to get an array as result containing
     * the entity and the calculated distance.
     *
     * @param Coordinates $coordinates
     * @param bool        $hidden
     *
     * @return QueryBuilder
     */
    public function createNearbyCommitteeQueryBuilder(Coordinates $coordinates, bool $hidden = true)
    {
        $hidden = $hidden ? 'hidden' : '';

        $exp = 'cos(radians(c.postAddress.latitude)) * cos(radians(c.postAddress.longitude) - radians(:longitude))';

        return $this
            ->createQueryBuilder('c')
            ->addSelect('('.self::EARTH_MEAN_RADIUS.' * acos(cos(radians(:latitude)) * '.$exp.' +
            sin(radians(:latitude)) * sin(radians(c.postAddress.latitude)))) as '.$hidden.' distance_between')
            ->setParameter('latitude', $coordinates->getLatitude())
            ->setParameter('longitude', $coordinates->getLongitude())
        ;
    }

    /**
     * @param int         $count
     * @param Coordinates $coordinates
     *
     * @return Committee[]
     */
    public function findNearbyCommittees(int $count, Coordinates $coordinates)
    {
        $query = $this
            ->createNearbyCommitteeQueryBuilder($coordinates)
            ->where('c.status = :status')
            ->setParameter('status', Committee::APPROVED)
            ->orderBy('distance_between', 'asc')
            ->setMaxResults($count)
            ->getQuery()
        ;

        return $query->getResult();
    }

    /**
     * Returns the total number of approved committees.
     *
     * @return int
     */
    public function countApprovedCommittees(): int
    {
        $query = $this
            ->createQueryBuilder('c')
            ->select('COUNT(c.uuid)')
            ->where('c.status = :status')
            ->setParameter('status', Committee::APPROVED)
            ->getQuery()
        ;

        return $query->getSingleScalarResult();
    }

    public function findCommittees(array $uuids, int $statusFilter = self::ONLY_APPROVED, int $limit = 0): array
    {
        if (!$uuids) {
            return [];
        }

        $qb = $this->createQueryBuilder('c');

        $qb
            ->where($qb->expr()->in('c.uuid', $uuids))
            ->orderBy('c.membersCounts', 'DESC')
        ;

        if (self::ONLY_APPROVED === $statusFilter) {
            $qb
                ->andWhere('c.status = :status')
                ->setParameter('status', Committee::APPROVED)
            ;
        }

        if ($limit >= 1) {
            $qb->setMaxResults($limit);
        }

        return $qb->getQuery()->getResult();
    }
}
