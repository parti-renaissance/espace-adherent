<?php

namespace AppBundle\Repository;

use AppBundle\Entity\CitizenInitiativeCategory;
use Doctrine\ORM\EntityRepository;

class CitizenInitiativeCategoryRepository extends EntityRepository
{
    /**
     * @return CitizenInitiativeCategory[]
     */
    public function findAllOrderedByName(): array
    {
        return $this->createQueryBuilder('c')->orderBy('c.name', 'ASC')->getQuery()->getResult();
    }
}
