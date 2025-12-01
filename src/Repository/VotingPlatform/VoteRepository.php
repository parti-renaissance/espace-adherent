<?php

declare(strict_types=1);

namespace App\Repository\VotingPlatform;

use App\Entity\Adherent;
use App\Entity\VotingPlatform\Designation\Designation;
use App\Entity\VotingPlatform\ElectionRound;
use App\Entity\VotingPlatform\Vote;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Query\Parameter;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends \Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository<\App\Entity\VotingPlatform\Vote>
 */
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
            ->setParameters(new ArrayCollection([new Parameter('adherent', $adherent), new Parameter('election_round', $electionRound)]))
            ->getQuery()
            ->getSingleScalarResult()
        ;
    }

    public function findVote(Adherent $adherent, ElectionRound $electionRound): ?Vote
    {
        return $this->createQueryBuilder('vote')
            ->innerJoin('vote.voter', 'voter')
            ->where('voter.adherent = :adherent AND vote.electionRound = :election_round')
            ->setParameters(new ArrayCollection([new Parameter('adherent', $adherent), new Parameter('election_round', $electionRound)]))
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }

    /**
     * @return Vote[]
     */
    public function findVoteForDesignation(Adherent $adherent, Designation $designation): array
    {
        return $this->createQueryBuilder('vote')
            ->innerJoin('vote.voter', 'voter')
            ->innerJoin('vote.electionRound', 'round')
            ->innerJoin('round.election', 'election')
            ->where('voter.adherent = :adherent')
            ->andWhere('election.designation = :designation')
            ->setParameters(new ArrayCollection([new Parameter('adherent', $adherent), new Parameter('designation', $designation)]))
            ->getQuery()
            ->getResult()
        ;
    }
}
