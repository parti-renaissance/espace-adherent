<?php

namespace App\Repository\VotingPlatform;

use App\Entity\Committee;
use App\Entity\VotingPlatform\Designation\Designation;
use App\Entity\VotingPlatform\Election;
use App\Entity\VotingPlatform\ElectionPool;
use App\Entity\VotingPlatform\ElectionRound;
use App\Entity\VotingPlatform\Vote;
use App\Entity\VotingPlatform\VoteChoice;
use App\Entity\VotingPlatform\Voter;
use App\Repository\UuidEntityRepositoryTrait;
use App\VotingPlatform\Election\ElectionStatusEnum;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\Query\Expr\Join;
use Doctrine\ORM\Query\Expr\Orx;
use Doctrine\Persistence\ManagerRegistry;

class ElectionRepository extends ServiceEntityRepository
{
    use UuidEntityRepositoryTrait;

    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Election::class);
    }

    public function hasElectionForCommittee(Committee $committee, Designation $designation): bool
    {
        return (bool) $this->createQueryBuilder('e')
            ->select('COUNT(1)')
            ->innerJoin('e.electionEntity', 'ee')
            ->where('ee.committee = :committee AND e.designation = :designation')
            ->setParameters([
                'committee' => $committee,
                'designation' => $designation,
            ])
            ->getQuery()
            ->getSingleScalarResult()
        ;
    }

    public function findOneForCommittee(
        Committee $committee,
        Designation $designation,
        bool $withResult = false
    ): ?Election {
        $qb = $this->createQueryBuilder('e')
            ->addSelect('d', 'ee')
            ->innerJoin('e.designation', 'd')
            ->innerJoin('e.electionEntity', 'ee')
            ->where('ee.committee = :committee')
            ->andWhere('d = :designation')
            ->setParameters([
                'committee' => $committee,
                'designation' => $designation,
            ])
            ->orderBy('d.voteStartDate', 'DESC')
        ;

        if ($withResult) {
            $qb
                ->addSelect('result', 'round_result', 'pool_result', 'group_result', 'candidate_group', 'candidate')
                ->leftJoin('e.electionResult', 'result')
                ->leftJoin('result.electionRoundResults', 'round_result')
                ->leftJoin('round_result.electionPoolResults', 'pool_result')
                ->leftJoin('pool_result.candidateGroupResults', 'group_result')
                ->leftJoin('group_result.candidateGroup', 'candidate_group')
                ->leftJoin('candidate_group.candidates', 'candidate')
            ;
        }

        return $qb->getQuery()->getOneOrNullResult();
    }

    public function findOneByDesignation(Designation $designation): ?Election
    {
        $qb = $this->createQueryBuilder('e')
            ->addSelect('d')
            ->innerJoin('e.designation', 'd')
            ->where('d = :designation')
            ->setParameters(['designation' => $designation])
            ->setMaxResults(1)
        ;

        if ($electionEntityIdentifier = $designation->getElectionEntityIdentifier()) {
            if ($designation->isCommitteeSupervisorType()) {
                $qb
                    ->innerJoin('e.electionEntity', 'election_entity')
                    ->innerJoin('election_entity.committee', 'committee', Join::WITH, 'committee.uuid = :committee_uuid')
                    ->setParameter('committee_uuid', $electionEntityIdentifier)
                ;
            }
        }

        return $qb->getQuery()->getOneOrNullResult();
    }

    /**
     * @return Election[]
     */
    public function findAllForDesignation(Designation $designation): array
    {
        return $this->createQueryBuilder('e')
            ->addSelect('d')
            ->innerJoin('e.designation', 'd')
            ->where('d = :designation')
            ->setParameters(['designation' => $designation])
            ->getQuery()
            ->getResult()
        ;
    }

    public function getSingleAggregatedData(ElectionRound $electionRound): array
    {
        return $this->createQueryBuilder('election')
            ->select('pool.code AS pool_code')
            ->addSelect(
                \sprintf(
                    '(SELECT COUNT(1)
                FROM %s AS pool2
                INNER JOIN pool2.candidateGroups AS candidate_groups
                WHERE pool2.id = pool.id) AS candidate_group_count',
                    ElectionPool::class
                ),
                \sprintf(
                    '(SELECT COUNT(1) FROM %s AS voter
                INNER JOIN voter.votersLists AS voters_list
                WHERE voters_list.election = election) AS voters_count',
                    Voter::class
                ),
                \sprintf(
                    '(SELECT COUNT(vote.id) FROM %s AS vote WHERE vote.electionRound = election_round) AS votes_count',
                    Vote::class
                ),
                \sprintf(
                    '(SELECT COUNT(vote_choice.id) FROM %s AS vote_choice
                    WHERE vote_choice.electionPool = pool AND vote_choice.isBlank = true) AS votes_blank_count',
                    VoteChoice::class
                ),
            )
            ->innerJoin('election.electionRounds', 'election_round')
            ->leftJoin('election.electionPools', 'pool')
            ->where('election_round = :election_round')
            ->setParameters([
                'election_round' => $electionRound,
            ])
            ->getQuery()
            ->getArrayResult()
        ;
    }

    /**
     * @return Election[]
     */
    public function getElectionsToClose(\DateTime $date, ?int $limit = null): array
    {
        $qb = $this->createQueryBuilder('election')
            ->addSelect('designation')
            ->innerJoin('election.designation', 'designation')
            ->where((new Orx())->addMultiple([
                'election.secondRoundEndDate IS NULL AND designation.voteEndDate IS NOT NULL AND designation.voteEndDate < :date',
                'election.secondRoundEndDate IS NOT NULL AND election.secondRoundEndDate < :date',
            ]))
            ->andWhere('election.status = :open')
            ->setParameters([
                'date' => $date,
                'open' => ElectionStatusEnum::OPEN,
            ])
            ->getQuery()
        ;

        if ($limit) {
            $qb->setMaxResults($limit);
        }

        return $qb->getResult();
    }

    /**
     * @return Election[]
     */
    public function getElectionsToCloseOrWithoutResults(\DateTime $date, ?int $limit = null): array
    {
        $qb = $this->createQueryBuilder('election')
            ->addSelect('designation')
            ->innerJoin('election.designation', 'designation')
            ->leftJoin('election.electionResult', 'election_result')
            ->where((new Orx())->addMultiple([
                'election.status = :open AND election.secondRoundEndDate IS NULL AND designation.voteEndDate IS NOT NULL AND designation.voteEndDate < :date',
                'election.status = :open AND election.secondRoundEndDate IS NOT NULL AND election.secondRoundEndDate < :date',
                'election.status != :open AND election_result IS NULL',
            ]))
            ->setParameters([
                'date' => $date,
                'open' => ElectionStatusEnum::OPEN,
            ])
            ->getQuery()
        ;

        if ($limit) {
            $qb->setMaxResults($limit);
        }

        return $qb->getResult();
    }
}
