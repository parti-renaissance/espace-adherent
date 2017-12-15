<?php

namespace AppBundle\Repository;

use AppBundle\Entity\CitizenProjectReport;
use Doctrine\ORM\EntityRepository;

class ReportRepository extends EntityRepository
{
    /**
     * @return int[]
     */
    public function findIdsByName(string $name): array
    {
        $ids = $this->getEntityManager()->getRepository(CitizenProjectReport::class)->createQueryBuilder('cpr')
            ->select('cpr.id')
            ->join('cpr.subject', 'cp')
            ->andWhere('cp.name LIKE :name')
            ->setParameter('name', sprintf('%%%s%%', $name))
            ->getQuery()
            ->getScalarResult()
        ;

        return array_column($ids, 'id');
    }
}
