<?php

namespace AppBundle\Repository;

use AppBundle\Entity\BaseGroup;
use Doctrine\Common\Collections\ArrayCollection;

class GroupRepository extends BaseGroupRepository
{
    /**
     * Returns the total number of approved groups.
     *
     * @return int
     */
    public function countApprovedGroups(): int
    {
        $query = $this
            ->createQueryBuilder('g')
            ->select('COUNT(g.uuid)')
            ->where('g.status = :status')
            ->setParameter('status', BaseGroup::APPROVED)
            ->getQuery()
        ;

        return $query->getSingleScalarResult();
    }

    public function findGroups(array $uuids, int $statusFilter = self::ONLY_APPROVED, int $limit = 0): ArrayCollection
    {
        if (!$uuids) {
            return new ArrayCollection();
        }

        $statuses[] = BaseGroup::APPROVED;
        if (self::INCLUDE_UNAPPROVED === $statusFilter) {
            $statuses[] = BaseGroup::PENDING;
        }

        $qb = $this->createQueryBuilder('c');

        $qb
            ->where($qb->expr()->in('c.uuid', $uuids))
            ->andWhere($qb->expr()->in('c.status', $statuses))
            ->orderBy('c.membersCounts', 'DESC')
        ;

        if ($limit >= 1) {
            $qb->setMaxResults($limit);
        }

        return new ArrayCollection($qb->getQuery()->getResult());
    }
}
