<?php

namespace App\Repository\TerritorialCouncil\ElectionPoll;

use App\Entity\TerritorialCouncil\ElectionPoll\Poll;
use App\Entity\TerritorialCouncil\ElectionPoll\Vote;
use App\Entity\TerritorialCouncil\TerritorialCouncilMembership;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Symfony\Bridge\Doctrine\RegistryInterface;

class VoteRepository extends ServiceEntityRepository
{
    public function __construct(RegistryInterface $registry)
    {
        parent::__construct($registry, Vote::class);
    }

    public function hasVoted(Poll $poll, TerritorialCouncilMembership $membership): bool
    {
        return (bool) $this->createQueryBuilder('vote')
            ->select('COUNT(1)')
            ->innerJoin('vote.choice', 'choice')
            ->where('vote.membership = :membership')
            ->andWhere('choice.electionPoll = :poll')
            ->setParameters([
                'poll' => $poll,
                'membership' => $membership,
            ])
            ->getQuery()
            ->getSingleScalarResult()
        ;
    }

    public function findOneForMembership(Poll $poll, TerritorialCouncilMembership $membership): ?Vote
    {
        return $this->createQueryBuilder('vote')
            ->innerJoin('vote.choice', 'choice')
            ->where('vote.membership = :membership')
            ->andWhere('choice.electionPoll = :poll')
            ->setParameters([
                'poll' => $poll,
                'membership' => $membership,
            ])
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
}
