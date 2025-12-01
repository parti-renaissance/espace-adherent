<?php

declare(strict_types=1);

namespace App\Repository\VotingPlatform;

use App\Entity\Committee;
use App\Entity\VotingPlatform\Candidate;
use App\Entity\VotingPlatform\CandidateGroup;
use App\Entity\VotingPlatform\Designation\Designation;
use App\Entity\VotingPlatform\Election;
use App\Entity\VotingPlatform\ElectionPool;
use App\Entity\VotingPlatform\ElectionRound;
use App\Entity\VotingPlatform\Vote;
use App\Entity\VotingPlatform\VoteChoice;
use App\Entity\VotingPlatform\Voter;
use App\Entity\VotingPlatform\VoteResult;
use App\Repository\UuidEntityRepositoryTrait;
use App\VotingPlatform\Election\ElectionStatusEnum;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Query\Expr\Join;
use Doctrine\ORM\Query\Expr\Orx;
use Doctrine\ORM\Query\Parameter;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends \Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository<\App\Entity\VotingPlatform\Election>
 */
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
            ->setParameters(new ArrayCollection([new Parameter('committee', $committee), new Parameter('designation', $designation)]))
            ->getQuery()
            ->getSingleScalarResult()
        ;
    }

    public function findOneForCommittee(
        Committee $committee,
        Designation $designation,
        bool $withResult = false,
    ): ?Election {
        $qb = $this->createQueryBuilder('e')
            ->addSelect('d', 'ee')
            ->innerJoin('e.designation', 'd')
            ->innerJoin('e.electionEntity', 'ee')
            ->where('ee.committee = :committee')
            ->andWhere('d = :designation')
            ->setParameters(new ArrayCollection([new Parameter('committee', $committee), new Parameter('designation', $designation)]))
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
            ->setParameters(new ArrayCollection([new Parameter('designation', $designation)]))
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
            ->setParameters(new ArrayCollection([new Parameter('designation', $designation)]))
            ->getQuery()
            ->getResult()
        ;
    }

    public function getSingleAggregatedData(ElectionRound $electionRound): array
    {
        return array_column($this->createQueryBuilder('election')
            ->select('pool.code AS pool_code')
            ->addSelect('pool.id')
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
            ->innerJoin('election.electionPools', 'pool')
            ->where('election_round = :election_round')
            ->setParameters(new ArrayCollection([new Parameter('election_round', $electionRound)]))
            ->getQuery()
            ->getArrayResult(), null, 'id');
    }

    /**
     * @return Election[]
     */
    public function getElectionsToClose(\DateTimeInterface $date, int $notification): array
    {
        return $this->createQueryBuilder('election')
            ->addSelect('designation')
            ->innerJoin('election.designation', 'designation')
            ->where(new Orx()->addMultiple([
                'election.secondRoundEndDate IS NULL AND designation.voteEndDate BETWEEN :start_date AND :end_date',
                'election.secondRoundEndDate IS NOT NULL AND election.secondRoundEndDate BETWEEN :start_date AND :end_date',
            ]))
            ->andWhere('election.status = :open')
            ->andWhere('designation.isCanceled = false')
            ->andWhere('BIT_AND(designation.notifications, :notification) > 0 AND BIT_AND(election.notificationsSent, :notification) = 0')
            ->andWhere(\sprintf('TIMESTAMPDIFF(%s, designation.voteStartDate, designation.voteEndDate) > 2', Designation::NOTIFICATION_VOTE_REMINDER_1H === $notification ? 'HOUR' : 'DAY'))
            ->setParameters(new ArrayCollection([new Parameter('start_date', $date), new Parameter('end_date', (clone $date)->modify('+1 '.(Designation::NOTIFICATION_VOTE_REMINDER_1H === $notification ? 'hour' : 'day'))), new Parameter('open', ElectionStatusEnum::OPEN), new Parameter('notification', $notification)]))
            ->getQuery()
            ->getResult()
        ;
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
            ->where(new Orx()->addMultiple([
                'election.status = :open AND election.secondRoundEndDate IS NULL AND designation.voteEndDate IS NOT NULL AND designation.voteEndDate < :date',
                'election.status = :open AND election.secondRoundEndDate IS NOT NULL AND election.secondRoundEndDate < :date',
                'election.status != :open AND election_result IS NULL',
            ]))
            ->setParameters(new ArrayCollection([new Parameter('date', $date), new Parameter('open', ElectionStatusEnum::OPEN)]))
            ->getQuery()
        ;

        if ($limit) {
            $qb->setMaxResults($limit);
        }

        return $qb->getResult();
    }

    public function findIncomingElections(\DateTimeInterface $date)
    {
        return $this->createQueryBuilder('election')
            ->addSelect('designation')
            ->innerJoin('election.designation', 'designation')
            ->where('designation.voteStartDate BETWEEN :start_date AND :end_date')
            ->andWhere('election.status = :open')
            ->andWhere('designation.isCanceled = false')
            ->andWhere('BIT_AND(designation.notifications, :notification) > 0 AND BIT_AND(election.notificationsSent, :notification) = 0')
            ->setParameters(new ArrayCollection([new Parameter('start_date', $date), new Parameter('end_date', (clone $date)->modify('+2 days')), new Parameter('open', ElectionStatusEnum::OPEN), new Parameter('notification', Designation::NOTIFICATION_VOTE_ANNOUNCEMENT)]))
            ->getQuery()
            ->getResult()
        ;
    }

    public function getLiveResults(ElectionRound $electionRound): array
    {
        $results = [];

        foreach ($electionRound->getElectionPools() as $pool) {
            if ($pool->isSeparator) {
                continue;
            }

            $poolResult = $this->createQueryBuilder('el')
                ->select(\sprintf(
                    '(SELECT COUNT(1) FROM %s AS t1
                    INNER JOIN t1.electionPool AS election_pool1
                    WHERE election_pool1.id = pool.id AND t1.isBlank = 0) AS expressed',
                    VoteChoice::class
                ))
                ->addSelect(\sprintf(
                    '(SELECT COUNT(1) FROM %s AS t2
                    INNER JOIN t2.electionPool AS election_pool2
                    WHERE election_pool2.id = pool.id AND t2.isBlank = 1) AS blank',
                    VoteChoice::class
                ))
                ->innerJoin('el.electionPools', 'pool')
                ->where('pool = :pool')
                ->setParameter('pool', $pool)
                ->getQuery()
                ->getSingleResult()
            ;

            $candidateGroupsResult = array_column($this->createQueryBuilder('el')
                ->select('candidate_group.id')
                ->addSelect('COUNT(DISTINCT vc.id) AS total')
                ->innerJoin('el.electionPools', 'pool')
                ->innerJoin('pool.candidateGroups', 'candidate_group')
                ->leftJoin(VoteChoice::class, 'vc', Join::WITH, 'vc.electionPool = pool AND vc.candidateGroup = candidate_group')
                ->where('pool = :pool')
                ->setParameter('pool', $pool)
                ->groupBy('candidate_group.id')
                ->getQuery()
                ->getArrayResult(), 'total', 'id');

            $results[] = [
                'expressed' => $expressed = $poolResult['expressed'],
                'blank' => $poolResult['blank'],
                'participated' => 0,
                'abstentions' => 0,
                'bulletin_count' => 0,
                'code' => $pool->getCode(),
                'candidate_group_results' => array_map(fn (CandidateGroup $candidateGroup) => [
                    'candidate_group' => [
                        'candidates' => $candidates = array_map(fn (Candidate $candidate) => [
                            'first_name' => $candidate->getFirstName(),
                            'last_name' => $candidate->getLastName(),
                            'gender' => $candidate->getGender(),
                        ], $candidateGroup->getCandidatesSorted(true)),
                        'elected' => false,
                        'title' => $candidates[0]['first_name'],
                    ],
                    'total' => $total = $candidateGroupsResult[$candidateGroup->getId()] ?? 0,
                    'rate' => $expressed > 0 ? round($total * 100.0 / $expressed, 2) : 0,
                ], $pool->getCandidateGroups()),
            ];
        }

        return $results;
    }

    public function getElectionStats(Designation $designation): array
    {
        return $this->createQueryBuilder('el')
            ->select(\sprintf('(
                SELECT COUNT(DISTINCT v1.id) FROM %s AS v1
                INNER JOIN v1.electionRound AS er1
                INNER JOIN er1.election AS e1
                INNER JOIN e1.designation AS d1
                WHERE d1.id = :designation_id
            ) AS voters', Vote::class))
            ->addSelect(\sprintf('(
                SELECT COUNT(DISTINCT v2.id) FROM %s AS v2
                INNER JOIN v2.electionRound AS er2
                INNER JOIN er2.election AS e2
                INNER JOIN e2.designation AS d2
                WHERE d2.id = :designation_id
            ) AS votes', VoteResult::class))
            ->setMaxResults(1)
            ->setParameters(new ArrayCollection([new Parameter('designation_id', $designation->getId())]))
            ->getQuery()
            ->getSingleResult()
        ;
    }
}
