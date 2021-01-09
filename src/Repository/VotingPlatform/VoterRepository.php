<?php

namespace App\Repository\VotingPlatform;

use App\Entity\Adherent;
use App\Entity\VotingPlatform\Election;
use App\Entity\VotingPlatform\ElectionRound;
use App\Entity\VotingPlatform\Vote;
use App\Entity\VotingPlatform\Voter;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\Query\Expr\Join;
use Doctrine\Persistence\ManagerRegistry;

class VoterRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Voter::class);
    }

    public function findForAdherent(Adherent $adherent): ?Voter
    {
        return $this->findOneBy(['adherent' => $adherent]);
    }

    public function countForElection(Election $election): int
    {
        return (int) $this->createQueryBuilder('voter')
            ->select('COUNT(1)')
            ->innerJoin('voter.votersLists', 'list')
            ->where('list.election = :election')
            ->setParameter('election', $election)
            ->getQuery()
            ->getSingleScalarResult()
        ;
    }

    public function existsForElection(Adherent $adherent, string $electionUuid): bool
    {
        return 0 < (int) $this->createQueryBuilder('voter')
            ->select('COUNT(1)')
            ->innerJoin('voter.votersLists', 'list')
            ->innerJoin('list.election', 'election')
            ->where('voter.adherent = :adherent AND election.uuid = :election_uuid')
            ->setParameters([
                'adherent' => $adherent,
                'election_uuid' => $electionUuid,
            ])
            ->getQuery()
            ->getSingleScalarResult()
        ;
    }

    public function findForElectionRound(ElectionRound $electionRound): array
    {
        return $this->createQueryBuilder('voter')
            ->select('adherent.firstName', 'adherent.lastName')
            ->addSelect(sprintf('(
                    SELECT v.votedAt
                    FROM %s AS v
                    WHERE v.voter = voter
                    AND v.electionRound = :electionRound
                ) as vote', Vote::class))
            ->innerJoin('voter.votersLists', 'list')
            ->leftJoin('voter.adherent', 'adherent')
            ->where('list.election = :election')
            ->setParameter('election', $electionRound->getElection())
            ->setParameter('electionRound', $electionRound)
            ->getQuery()
            ->getArrayResult()
        ;
    }

    /**
     * @return Voter[]
     */
    public function findForElection(Election $election, bool $partial = false): array
    {
        return $this->createQueryBuilder('voter')
            ->addSelect($partial ? 'PARTIAL adherent.{id, uuid, emailAddress, firstName, lastName}' : 'adherent')
            ->innerJoin('voter.votersLists', 'list')
            ->innerJoin('voter.adherent', 'adherent')
            ->where('list.election = :election')
            ->andWhere('voter.isGhost = :false')
            ->setParameter('election', $election)
            ->setParameter('false', false)
            ->getQuery()
            ->getResult()
        ;
    }

    /**
     * @return Voter[]|array
     */
    public function findVotersToRemindForElection(Election $election): array
    {
        return $this->createQueryBuilder('voter')
            ->addSelect('adherent')
            ->innerJoin('voter.votersLists', 'list')
            ->innerJoin('list.election', 'election')
            ->innerJoin('voter.adherent', 'adherent')
            ->leftJoin(Vote::class, 'vote', Join::WITH, 'vote.voter = voter AND vote.electionRound = :current_round')
            ->andWhere('list.election = :election')
            ->andWhere('vote IS NULL')
            ->setParameters([
                'election' => $election,
                'current_round' => $election->getCurrentRound(),
            ])
            ->getQuery()
            ->getResult()
        ;
    }

    /**
     * @return Voter[]
     */
    public function findVotedForElection(Election $election): array
    {
        return $this->createQueryBuilder('voter')
            ->addSelect('adherent')
            ->innerJoin('voter.adherent', 'adherent')
            ->innerJoin(Vote::class, 'vote', Join::WITH, 'vote.voter = voter AND vote.electionRound IN (:election_rounds)')
            ->setParameters([
                'election_rounds' => $election->getElectionRounds(),
            ])
            ->groupBy('voter.id')
            ->getQuery()
            ->getResult()
        ;
    }
}
