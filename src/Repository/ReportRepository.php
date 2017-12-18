<?php

namespace AppBundle\Repository;

use AppBundle\Entity\Adherent;
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

    public function anonymizeAuthorReports(Adherent $adherent)
    {
        $qb = $this->createQueryBuilder('r');
        $qb->update()
            ->set('r.author', ':new_value')
            ->setParameter('new_value', null)
            ->where('r.author = :author')
            ->setParameter('author', $adherent);

        return $qb->getQuery()->execute();
    }
}
