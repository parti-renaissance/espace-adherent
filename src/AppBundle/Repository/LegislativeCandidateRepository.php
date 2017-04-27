<?php

namespace AppBundle\Repository;

use AppBundle\Entity\LegislativeCandidate;
use Doctrine\ORM\EntityRepository;

class LegislativeCandidateRepository extends EntityRepository
{
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
    public function findAllForDirectory(): array
    {
        return $this
            ->createQueryBuilder('lc')
            ->addSelect('dz', 'md', '(CASE WHEN lc.media IS NOT NULL THEN 1 ELSE 0 END) AS HIDDEN has_picture')
            ->leftJoin('lc.districtZone', 'dz')
            ->leftJoin('lc.media', 'md')
            ->orderBy('has_picture', 'DESC')
            ->addOrderBy('dz.rank', 'ASC')
            ->addOrderBy('lc.districtNumber', 'ASC')
            ->getQuery()
            ->getResult()
        ;
    }
}
