<?php

namespace App\Repository\VotingPlatform;

use App\Entity\Adherent;
use App\Entity\VotingPlatform\ElectionRound;
use App\Entity\VotingPlatform\Vote;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Common\Persistence\ManagerRegistry;

class VoteRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Vote::class);
    }

    public function alreadyVoted(Adherent $adherent, ElectionRound $electionRound): bool
    {
        return 0 < (int) $this->createQueryBuilder('vote')
            ->select('COUNT(1)')
            ->innerJoin('vote.voter', 'voter')
            ->where('voter.adherent = :adherent AND vote.electionRound = :election_round')
            ->setParameters([
                'adherent' => $adherent,
                'election_round' => $electionRound,
            ])
            ->getQuery()
            ->getSingleScalarResult()
        ;
    }

    public function findVote(Adherent $adherent, ElectionRound $electionRound): ?Vote
    {
        return $this->createQueryBuilder('vote')
            ->innerJoin('vote.voter', 'voter')
            ->where('voter.adherent = :adherent AND vote.electionRound = :election_round')
            ->setParameters([
                'adherent' => $adherent,
                'election_round' => $electionRound,
            ])
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
}
