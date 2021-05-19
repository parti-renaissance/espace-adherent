<?php

namespace App\Repository\Poll;

use App\Entity\Geo\Zone;
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
        $qb = $this
            ->createQueryBuilder('poll')
            ->innerJoin('poll.zone', 'zone')
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
            ->where('(zone IN (:zones))')
            ->setParameter('zones', $zones)
            ->setParameter('yes', Choice::YES)
            ->setParameter('no', Choice::NO)
            ->orderBy('poll.createdAt', 'DESC')
        ;

        return $qb
            ->getQuery()
            ->getResult()
        ;
    }

    public function findOnePublishedByZone(Zone $zone): ?LocalPoll
    {
        return $this
            ->createQueryBuilder('poll')
            ->where('poll.published = :true AND poll.finishAt > :now AND poll.zone = :zone')
            ->orderBy('poll.finishAt', 'desc')
            ->setParameters([
                'zone' => $zone,
                'true' => 1,
                'now' => new \DateTime(),
            ])
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
}
