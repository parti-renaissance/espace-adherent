<?php

namespace App\Repository\VotingPlatform;

use App\Entity\VotingPlatform\Designation\Designation;
use App\Repository\GeoZoneTrait;
use App\VotingPlatform\Designation\DesignationTypeEnum;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
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
    public function getDesignations(array $types, int $limit = null): array
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
            ->andWhere('(d.candidacyEndDate IS NULL OR d.candidacyEndDate > :date)')
            ->andWhere('d.limited = :false')
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
    public function getWithFinishCandidacyPeriod(\DateTime $candidacyStartDate): array
    {
        return $this->createQueryBuilder('d')
            ->where('d.candidacyStartDate <= :date')
            ->andWhere('d.candidacyEndDate < :date')
            ->andWhere('d.voteStartDate > :date')
            ->setParameter('date', $candidacyStartDate)
            ->getQuery()
            ->getResult()
        ;
    }

    public function findAllActiveForZones(array $zones, array $types = [], int $limit = null): array
    {
        $queryBuilder = $this->createQueryBuilder('designation')
            ->where('designation.voteStartDate IS NOT NULL AND designation.voteEndDate IS NOT NULL')
            ->andWhere('designation.voteEndDate > :date')
            ->setParameters([
                'date' => new \DateTime(),
            ])
            ->setMaxResults($limit)
            ->orderBy('designation.voteStartDate', 'ASC')
        ;

        if ($types) {
            $queryBuilder
                ->andWhere('designation.type IN (:types)')
                ->setParameter('types', $types)
            ;
        }

        $this->withGeoZones(
            $zones,
            $queryBuilder,
            'designation',
            Designation::class,
            'd2',
            'zones',
            'z2'
        );

        return $queryBuilder->getQuery()->getResult();
    }

    public function findFirstActiveLocalPollForZones(array $zones): ?Designation
    {
        if ($designations = $this->findAllActiveForZones($zones, [DesignationTypeEnum::LOCAL_POLL], 1)) {
            return current($designations);
        }

        return null;
    }
}
