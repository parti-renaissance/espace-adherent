<?php

namespace AppBundle\Repository;

use AppBundle\Entity\Referent;
use Doctrine\ORM\EntityRepository;

class ReferentRepository extends EntityRepository
{
    public function findByStatus($status = Referent::ENABLED)
    {
        $qb = $this->createQueryBuilder('lc');

        $qb
            ->where('lc.status = :status')
            ->setParameter('status', $status)
        ;

        return $qb->getQuery()->getResult();
    }
}
