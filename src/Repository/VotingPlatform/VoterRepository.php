<?php

declare(strict_types=1);

namespace App\Repository\VotingPlatform;

use App\Entity\Adherent;
use App\Entity\Committee;
use App\Entity\VotingPlatform\Election;
use App\Entity\VotingPlatform\ElectionRound;
use App\Entity\VotingPlatform\Vote;
use App\Entity\VotingPlatform\Voter;
use App\VotingPlatform\Designation\DesignationTypeEnum;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Query;
use Doctrine\ORM\Query\Expr\Join;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends \Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository<\App\Entity\VotingPlatform\Voter>
 */
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
            ->setParameters(new ArrayCollection([new Query\Parameter('adherent', $adherent), new Query\Parameter('election_uuid', $electionUuid)]))
            ->getQuery()
            ->getSingleScalarResult()
        ;
    }

    public function findForElectionRound(ElectionRound $electionRound): array
    {
        return $this->createQueryBuilder('voter')
            ->select('adherent.firstName', 'adherent.lastName')
            ->addSelect(\sprintf('(
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

    public function getVotersForElection(Election $election): array
    {
        return $this->createQueryBuilder('voter')
            ->select(
                'adherent.firstName AS first_name',
                'adherent.lastName AS last_name',
                'adherent.postAddress.postalCode AS postal_code',
                'vote.votedAt AS voted_at',
            )
            ->innerJoin('voter.votersLists', 'list')
            ->innerJoin('voter.adherent', 'adherent')
            ->leftJoin(Vote::class, 'vote', Join::WITH, 'vote.voter = voter AND vote.electionRound = :election_round')
            ->where('list.election = :election')
            ->setParameter('election', $election)
            ->setParameter('election_round', $election->getCurrentRound())
            ->getQuery()
            ->getScalarResult()
        ;
    }

    /**
     * @return Voter[]
     */
    public function findForElection(
        Election $election,
        bool $partial = false,
        ?int $offset = null,
        ?int $limit = null,
        bool $excludeVoted = false,
    ): array {
        $queryBuilder = $this->createQueryBuilder('voter')
            ->addSelect($partial ? 'PARTIAL adherent.{id, uuid, emailAddress, firstName, lastName}' : 'adherent')
            ->innerJoin('voter.votersLists', 'list')
            ->innerJoin('voter.adherent', 'adherent')
            ->where('list.election = :election')
            ->andWhere('voter.isGhost = :false')
            ->andWhere('adherent.status = :active')
            ->setParameter('active', Adherent::ENABLED)
            ->setParameter('election', $election)
            ->setParameter('false', false)
        ;

        if ($excludeVoted) {
            $queryBuilder
                ->leftJoin(Vote::class, 'vote', Join::WITH, 'vote.voter = voter AND vote.electionRound = :current_round')
                ->andWhere('vote IS NULL')
                ->setParameter('current_round', $election->getCurrentRound())
            ;
        }

        if (null !== $offset && null !== $limit) {
            $queryBuilder
                ->setMaxResults($limit)
                ->setFirstResult($offset)
                ->orderBy('voter.id')
            ;
        }

        $query = $queryBuilder->getQuery();

        if ($partial) {
            $query->setHint(Query::HINT_FORCE_PARTIAL_LOAD, true);
        }

        return $query->getResult();
    }

    public function isInVoterListForCommitteeElection(
        Adherent $adherent,
        ?Committee $committee = null,
        ?\DateTimeInterface $after = null,
    ): bool {
        $qb = $this->createQueryBuilder('voter')
                ->select('COUNT(1)')
                ->innerJoin('voter.votersLists', 'list')
                ->innerJoin('list.election', 'election')
                ->innerJoin('election.designation', 'designation', Join::WITH, 'designation.type = :designation_type')
                ->innerJoin('election.electionEntity', 'election_entity')
                ->innerJoin('election_entity.committee', 'committee')
                ->andWhere('voter.adherent = :adherent')
                ->setParameters(new ArrayCollection([new Query\Parameter('adherent', $adherent), new Query\Parameter('designation_type', DesignationTypeEnum::COMMITTEE_SUPERVISOR)]))
        ;

        if ($committee) {
            $qb
                ->andWhere('committee = :committee')
                ->setParameter('committee', $committee)
            ;
        }

        if ($after) {
            $qb
                ->andWhere('designation.voteEndDate >= :after')
                ->setParameter('after', $after)
            ;
        }

        return 0 < (int) $qb
            ->getQuery()
            ->getSingleScalarResult()
        ;
    }
}
