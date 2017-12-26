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
     * @param string|null $status
     *
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
