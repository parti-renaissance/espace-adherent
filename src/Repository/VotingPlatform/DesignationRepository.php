<?php

namespace App\Repository\VotingPlatform;

use App\Entity\Adherent;
use App\Entity\VotingPlatform\Designation\Designation;
use App\Entity\VotingPlatform\Election;
use App\Repository\GeoZoneTrait;
use App\VotingPlatform\Designation\DesignationTypeEnum;
use App\VotingPlatform\Election\ElectionStatusEnum;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\Query\Expr\Join;
use Doctrine\Persistence\ManagerRegistry;

class DesignationRepository extends ServiceEntityRepository
{
    use GeoZoneTrait;

    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Designation::class);
    }

    /**
     * @return Designation[]
     */
    public function getIncomingDesignations(\DateTime $voteStartDate): array
    {
        return $this->createQueryBuilder('d')
            ->where('d.voteStartDate IS NOT NULL AND d.voteEndDate IS NOT NULL')
            ->andWhere('d.isCanceled = false')
            ->andWhere(
                '(
                    d.voteStartDate <= :date
                    OR (
                        d.electionCreationDate IS NOT NULL
                        AND d.electionCreationDate <= :date
                    )
                ) AND d.voteEndDate > :date'
            )
            ->setParameter('date', $voteStartDate)
            ->getQuery()
            ->getResult()
        ;
    }

    /**
     * @return Designation[]
     */
    public function getDesignations(array $types, ?int $limit = null): array
    {
        $qb = $this->createQueryBuilder('d')
            ->orderBy('d.voteStartDate', 'DESC')
        ;

        if ($types) {
            $qb
                ->andWhere('d.type IN (:types)')
                ->setParameter('types', $types)
            ;
        }

        if ($limit) {
            $qb->setMaxResults($limit);
        }

        return $qb->getQuery()->getResult();
    }

    /**
     * @return Designation[]
     */
    public function getIncomingCandidacyDesignations(\DateTime $candidacyStartDate): array
    {
        return $this->createQueryBuilder('d')
            ->where('d.candidacyStartDate <= :date')
            ->andWhere('d.isCanceled = false')
            ->andWhere('(d.candidacyEndDate IS NULL OR d.candidacyEndDate > :date)')
            ->andWhere('(d.limited = :false OR d.electionEntityIdentifier IS NOT NULL)')
            ->setParameters([
                'date' => $candidacyStartDate,
                'false' => false,
            ])
            ->getQuery()
            ->getResult()
        ;
    }

    /**
     * @return Designation[]
     */
    public function getWithFinishCandidacyPeriod(\DateTimeInterface $candidacyStartDate, array $types): array
    {
        return $this->createQueryBuilder('d')
            ->where('d.candidacyStartDate <= :date')
            ->andWhere('d.isCanceled = false')
            ->andWhere('d.candidacyEndDate < :date AND d.voteStartDate > :date')
            ->andWhere('d.type IN (:types)')
            ->setParameter('date', $candidacyStartDate)
            ->setParameter('types', $types)
            ->getQuery()
            ->getResult()
        ;
    }

    /**
     * @return Designation[]
     */
    public function getWithActiveResultsPeriod(\DateTimeInterface $date): array
    {
        return $this->createQueryBuilder('designation')
            ->innerJoin(Election::class, 'election', Join::WITH, 'election.designation = designation')
            ->innerJoin('election.electionResult', 'election_result')
            ->where('designation.resultDisplayDelay > 0 AND election.status = :close_status')
            ->andWhere('designation.isCanceled = false')
            ->andWhere('BIT_AND(designation.notifications, :notification) > 0 AND BIT_AND(election.notificationsSent, :notification) = 0')
            ->andWhere('DATE_ADD(designation.voteEndDate, designation.resultScheduleDelay, \'HOUR\') < :now')
            ->andWhere('DATE_ADD(DATE_ADD(designation.voteEndDate, designation.resultScheduleDelay, \'HOUR\'), designation.resultDisplayDelay, \'DAY\') > :now')
            ->setParameters([
                'now' => $date,
                'close_status' => ElectionStatusEnum::CLOSED,
                'notification' => Designation::NOTIFICATION_RESULT_READY,
            ])
            ->getQuery()
            ->getResult()
        ;
    }

    public function findAllActiveForAdherent(Adherent $adherent, array $types = [], ?int $limit = null, bool $withVoteActiveOnly = false): array
    {
        $queryBuilder = $this->createQueryBuilder('designation')
            ->addSelect(
                'CASE
                    WHEN (designation.voteStartDate < :now AND designation.voteEndDate > :now) THEN 10
                    WHEN (designation.voteEndDate < :now AND designation.resultDisplayDelay > 0 AND DATE_ADD(designation.voteEndDate, designation.resultDisplayDelay, \'DAY\') > :now) THEN 1
                    ELSE 0
                END AS HIDDEN score'
            )
            ->where('DATE_ADD(designation.voteEndDate, 3, \'DAY\') > :now')
            ->andWhere('designation.alertBeginAt IS NOT NULL AND designation.alertBeginAt < :now')
            ->andWhere('designation.isCanceled = false')
            ->setParameters(['now' => new \DateTime()])
            ->setMaxResults($limit)
            ->orderBy('score', 'DESC')
            ->addOrderBy('designation.voteStartDate', 'ASC')
        ;

        if ($withVoteActiveOnly) {
            $queryBuilder->andWhere('designation.voteEndDate > :now');
        }

        $conditions = $queryBuilder->expr()->orX();

        if ($types) {
            $queryBuilder
                ->andWhere('designation.type IN (:types)')
                ->setParameter('types', $types)
            ;

            if ($nationalTypes = array_intersect($types, DesignationTypeEnum::NATIONAL_TYPES)) {
                $conditions->add('designation.type IN (:national_types)');
                $queryBuilder->setParameter('national_types', $nationalTypes);
            }
        }

        if ($zones = $adherent->getParentZones()) {
            $zoneQueryBuilder = $this->createGeoZonesQueryBuilder(
                'designation',
                $zones,
                $queryBuilder,
                Designation::class,
                'd2',
                'zones',
                'z2',
                null,
                false
            );

            $conditions->add($queryBuilder->expr()->exists($zoneQueryBuilder->getDQL()));
        }

        $votingPlatformElectionQueryBuilder = $this->getEntityManager()->createQueryBuilder()
            ->select('DISTINCT d3.id')
            ->from(Election::class, 'voting_platform_election')
            ->innerJoin('voting_platform_election.designation', 'd3')
            ->innerJoin('voting_platform_election.votersList', 'voter_list')
            ->innerJoin('voter_list.voters', 'voter')
            ->where('voter.adherent = :adherent')
        ;
        $queryBuilder->setParameter('adherent', $adherent);

        if ($types) {
            $votingPlatformElectionQueryBuilder->andWhere('d3.type IN (:d3_types)');
            $queryBuilder->setParameter('d3_types', $types);
        }

        if ($withVoteActiveOnly) {
            $queryBuilder->andWhere(\sprintf('designation.id IN (%s)', $votingPlatformElectionQueryBuilder->getDQL()));
        } else {
            $conditions->add(\sprintf('designation.id IN (%s)', $votingPlatformElectionQueryBuilder->getDQL()));
        }

        $queryBuilder->andWhere($conditions);

        return $queryBuilder->getQuery()->getResult();
    }
}
