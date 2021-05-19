<?php

namespace App\Repository\Poll;

use App\Entity\Poll\NationalPoll;
use App\Entity\Poll\Poll;
use Cake\Chronos\Chronos;
use Doctrine\Persistence\ManagerRegistry;

class NationalPollRepository extends AbstractPollRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, NationalPoll::class);
    }

    public function findLastActivePoll(): ?Poll
    {
        return $this->createQueryBuilder('poll')
            ->where('poll.finishAt > :now')
            ->orderBy('poll.finishAt', 'desc')
            ->setParameter('now', new Chronos())
            ->setMaxResults(1)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
}
