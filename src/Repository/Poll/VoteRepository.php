<?php

declare(strict_types=1);

namespace App\Repository\Poll;

use App\Entity\Adherent;
use App\Entity\Poll\Poll;
use App\Entity\Poll\Vote;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

class VoteRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Vote::class);
    }

    public function hasVoted(Poll $poll, Adherent $adherent): bool
    {
        return (bool) $this->createQueryBuilder('vote')
            ->select('COUNT(vote.id)')
            ->innerJoin('vote.choice', 'choice')
            ->where('choice.poll = :poll')
            ->andWhere('vote.adherent = :adherent')
            ->setParameter('poll', $poll)
            ->setParameter('adherent', $adherent)
            ->getQuery()
            ->getSingleScalarResult()
        ;
    }
}
