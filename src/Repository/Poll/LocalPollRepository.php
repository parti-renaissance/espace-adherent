<?php

namespace App\Repository\Poll;

use App\Entity\Poll\Choice;
use App\Entity\Poll\LocalPoll;
use App\Entity\Poll\Vote;
use Doctrine\Persistence\ManagerRegistry;

class LocalPollRepository extends AbstractPollRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, LocalPoll::class);
    }

    /**
     * @return LocalPoll[]
     */
    public function findAllByZonesWithStats(array $zones): array
    {
        return $this
            ->createQueryBuilder('poll')
            ->innerJoin('poll.zone', 'zone')
            ->innerJoin('zone.parents', 'parent')
            ->innerJoin('zone.children', 'child')
            ->addSelect('zone')
            ->addSelect(sprintf('(
                SELECT COUNT(vote_y.id) FROM %s AS vote_y
                INNER JOIN vote_y.choice AS choice_y
                WHERE choice_y.value = :yes AND choice_y.poll = poll
            ) AS yes_count', Vote::class))
            ->addSelect(sprintf('(
                SELECT COUNT(vote_n.id) FROM %s AS vote_n
                INNER JOIN vote_n.choice AS choice_n
                WHERE choice_n.value = :no AND choice_n.poll = poll
            ) AS no_count', Vote::class))
            ->where('(zone IN (:zones) OR parent IN (:zones) OR child IN (:zones))')
            ->setParameter('zones', $zones)
            ->setParameter('yes', Choice::YES)
            ->setParameter('no', Choice::NO)
            ->orderBy('poll.createdAt', 'DESC')
            ->getQuery()
            ->getResult()
        ;
    }
}
