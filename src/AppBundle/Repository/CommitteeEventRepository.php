<?php

namespace AppBundle\Repository;

use AppBundle\Entity\CommitteeEvent;
use Doctrine\ORM\EntityRepository;

class CommitteeEventRepository extends EntityRepository
{
    public function findBySlug(string $slug): ?CommitteeEvent
    {
        return $this->findOneBy(['slug' => $slug]);
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
