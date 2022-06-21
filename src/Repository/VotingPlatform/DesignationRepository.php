<?php

namespace App\Repository\VotingPlatform;

use App\Entity\VotingPlatform\Designation\Designation;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

class DesignationRepository extends ServiceEntityRepository
{
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
            ->andWhere('d.voteStartDate <= :date AND d.voteEndDate > :date')
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
}
