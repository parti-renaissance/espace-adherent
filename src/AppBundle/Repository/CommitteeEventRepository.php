<?php

namespace AppBundle\Repository;

use AppBundle\Entity\Committee;
use AppBundle\Entity\CommitteeEvent;
use Doctrine\ORM\EntityRepository;

class CommitteeEventRepository extends EntityRepository
{
    public function findOneBySlug(string $slug): ?CommitteeEvent
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

    public function findMostRecentCommitteeEvent(): ?CommitteeEvent
    {
        $query = $this
            ->createQueryBuilder('ce')
            ->orderBy('ce.createdAt', 'DESC')
            ->getQuery();

        return $query->getOneOrNullResult();
    }
}
