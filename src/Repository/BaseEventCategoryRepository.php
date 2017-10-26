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
}
