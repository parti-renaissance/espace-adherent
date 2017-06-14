<?php

namespace AppBundle\Repository;

use AppBundle\Entity\EventCategory;
use Doctrine\ORM\EntityRepository;

class EventCategoryRepository extends EntityRepository
{
    /**
     * @return EventCategory[]
     */
    public function findAllOrderedByName(): array
    {
        return $this->createQueryBuilder('c')->orderBy('c.name', 'ASC')->getQuery()->getResult();
    }
}
