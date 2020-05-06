<?php

namespace App\Repository;

use App\Entity\LegislativeCandidate;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Symfony\Bridge\Doctrine\RegistryInterface;

class LegislativeCandidateRepository extends ServiceEntityRepository
{
    public function __construct(RegistryInterface $registry)
    {
        parent::__construct($registry, LegislativeCandidate::class);
    }

    public function findDistrictZoneCandidate(string $areaCode, string $areaNumber): ?LegislativeCandidate
    {
        return $this
            ->createQueryBuilder('lc')
            ->leftJoin('lc.districtZone', 'dz')
            ->where('dz.areaCode = :areaCode')
            ->andWhere('lc.districtNumber = :areaNumber')
            ->setParameter('areaCode', $areaCode)
            ->setParameter('areaNumber', $areaNumber)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }

    /**
     * @return LegislativeCandidate[]
     */
    public function findAllForDirectory(string $status = null): array
    {
        $qb = $this->createQueryBuilder('lc');
        $qb
            ->addSelect('dz', 'md')
            ->leftJoin('lc.districtZone', 'dz')
            ->leftJoin('lc.media', 'md')
            ->addOrderBy('dz.rank', 'ASC')
            ->addOrderBy('lc.districtNumber', 'ASC')
        ;

        if ($status) {
            $qb
                ->where('lc.status = :status')
                ->setParameter('status', $status)
            ;
        }

        return $qb->getQuery()->getResult();
    }
}
