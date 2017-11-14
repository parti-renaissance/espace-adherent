<?php

namespace AppBundle\Repository;

use AppBundle\Entity\BaseEventCategory;
use Doctrine\ORM\EntityRepository;

abstract class BaseEventCategoryRepository extends EntityRepository
{
    /**
     * @return BaseEventCategory[]
     */
    public function findAllOrderedByName(): array
    {
        return $this->createQueryBuilder('c')->orderBy('c.name', 'ASC')->getQuery()->getResult();
    }

    public function findAllEnabledOrderedByName(): array
    {
        return $this
            ->createQueryBuilder('c')
            ->where('c.status = :status')
            ->setParameter('status', BaseEventCategory::ENABLED)
            ->orderBy('c.name', 'ASC')
            ->getQuery()
            ->getResult();
    }
}
