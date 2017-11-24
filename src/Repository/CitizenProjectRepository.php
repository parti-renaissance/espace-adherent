<?php

namespace AppBundle\Repository;

use AppBundle\Coordinator\Filter\CitizenProjectFilter;
use AppBundle\Entity\Adherent;
use AppBundle\Entity\BaseGroup;
use AppBundle\Entity\CitizenProject;
use Doctrine\Common\Collections\ArrayCollection;

class CitizenProjectRepository extends BaseGroupRepository
{
    /**
     * Returns the total number of approved citizen projects.
     *
     * @return int
     */
    public function countApprovedCitizenProjects(): int
    {
        return $this
            ->createQueryBuilder('g')
            ->select('COUNT(g.uuid)')
            ->where('g.status = :status')
            ->setParameter('status', BaseGroup::APPROVED)
            ->getQuery()
            ->getSingleScalarResult()
        ;
    }

    public function findCitizenProjects(array $uuids, int $statusFilter = self::ONLY_APPROVED, int $limit = 0): ArrayCollection
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

    public function findOneApprovedBySlug(string $slug): ?CitizenProject
    {
        return $this
            ->createQueryBuilder('g')
            ->where('g.slug = :slug')
            ->andWhere('g.status = :status')
            ->setParameter('slug', $slug)
            ->setParameter('status', BaseGroup::APPROVED)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }

    public function findManagedByCoordinator(Adherent $coordinator, CitizenProjectFilter $filter): array
    {
        if (!$coordinator->isCoordinatorCitizenProjectSector()) {
            return [];
        }

        $qb = $this->createQueryBuilder('cp')
            ->orderBy('cp.name', 'ASC')
            ->orderBy('cp.createdAt', 'DESC');

        $filter->setCoordinator($coordinator);
        $filter->apply($qb, 'cp');

        return $qb->getQuery()->getResult();
    }
}
