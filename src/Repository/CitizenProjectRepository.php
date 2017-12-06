<?php

namespace AppBundle\Repository;

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

    public function hasCitizenProjectInStatus(Adherent $adherent, array $status): bool
    {
        $nb = $this->createQueryBuilder('cp')
            ->select('COUNT(cp) AS nb')
            ->where('cp.createdBy = :creator')
            ->andWhere('cp.status IN (:status)')
            ->setParameter('creator', $adherent->getUuid()->toString())
            ->setParameter('status', $status)
            ->getQuery()
            ->getSingleScalarResult();

        return $nb > 0;
    }
}
