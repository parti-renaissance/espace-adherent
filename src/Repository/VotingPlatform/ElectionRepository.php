<?php

namespace App\Repository\VotingPlatform;

use App\Entity\Committee;
use App\Entity\VotingPlatform\Designation\Designation;
use App\Entity\VotingPlatform\Election;
use App\Entity\VotingPlatform\Vote;
use App\Entity\VotingPlatform\Voter;
use App\ValueObject\Genders;
use App\VotingPlatform\Election\ElectionStatusEnum;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Common\Persistence\ManagerRegistry;
use Doctrine\ORM\Query;
use Doctrine\ORM\Query\Expr\Orx;

class ElectionRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Election::class);
    }

    public function findByUuid(string $uuid): ?Election
    {
        return $this->findOneBy(['uuid' => $uuid]);
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

    public function findOneForCommittee(Committee $committee, Designation $designation): ?Election
    {
        return $this->createQueryBuilder('e')
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
            ->setMaxResults(1)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }

    public function getAllAggregatedDataForCommittee(Committee $committee): array
    {
        return $this->createQueryBuilder('election')
            ->select('election', 'designation')
            ->addSelect(...$this->getElectionStatsSelectParts())
            ->innerJoin('election.designation', 'designation')
            ->innerJoin('election.electionEntity', 'election_entity')
            ->innerJoin('election.electionPools', 'pool')
            ->innerJoin('pool.candidateGroups', 'candidate_groups')
            ->innerJoin('candidate_groups.candidates', 'candidate')
            ->innerJoin('election.electionRounds', 'election_round')
            ->where('election_entity.committee = :committee')
            ->andWhere('election_round.isActive = :true')
            ->setParameters([
                'committee' => $committee,
                'male' => Genders::MALE,
                'female' => Genders::FEMALE,
                'true' => true,
            ])
            ->orderBy('designation.voteEndDate', 'DESC')
            ->groupBy('election.id')
            ->getQuery()
            ->getArrayResult()
        ;
    }

    public function getSingleAggregatedData(Election $election): array
    {
        return $this->createQueryBuilder('election')
            ->addSelect(...$this->getElectionStatsSelectParts())
            ->innerJoin('election.electionRounds', 'election_round')
            ->innerJoin('election.electionPools', 'pool')
            ->innerJoin('pool.candidateGroups', 'candidate_groups')
            ->innerJoin('candidate_groups.candidates', 'candidate')
            ->where('election = :election')
            ->andWhere('election_round.isActive = :true')
            ->setParameters([
                'election' => $election,
                'male' => Genders::MALE,
                'female' => Genders::FEMALE,
                'true' => true,
            ])
            ->getQuery()
            ->getSingleResult(Query::HYDRATE_ARRAY)
        ;
    }

    private function getElectionStatsSelectParts(): array
    {
        return [
            'SUM(IF(candidate.gender = :female, 1, 0)) as woman_count',
            'SUM(IF(candidate.gender = :male, 1, 0)) as man_count',
            sprintf(
                '(SELECT COUNT(1) FROM %s AS voter
                INNER JOIN voter.votersLists AS voters_list
                WHERE voters_list.election = election) AS voters_count',
                Voter::class
            ),
            sprintf(
                '(SELECT COUNT(vote.id) FROM %s AS vote WHERE vote.electionRound = election_round) AS votes_count',
                Vote::class
            ),
        ];
    }

    /**
     * @return Election[]
     */
    public function getElectionsToClose(\DateTime $date, int $limit = 50): array
    {
        return $this->createQueryBuilder('election')
            ->addSelect('designation')
            ->innerJoin('election.designation', 'designation')
            ->where((new Orx())->addMultiple([
                'election.secondRoundEndDate IS NULL AND designation.voteEndDate < :date',
                'election.secondRoundEndDate IS NOT NULL AND election.secondRoundEndDate < :date',
            ]))
            ->andWhere('election.status = :open')
            ->setParameters([
                'date' => $date,
                'open' => ElectionStatusEnum::OPEN,
            ])
            ->getQuery()
            ->setMaxResults($limit)
            ->getResult()
        ;
    }
}
