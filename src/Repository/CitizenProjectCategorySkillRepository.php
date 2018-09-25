<?php

namespace AppBundle\Repository;

use AppBundle\Entity\CitizenProjectCategory;
use Doctrine\ORM\EntityRepository;

class CitizenProjectCategorySkillRepository extends EntityRepository
{
    public function findByCitizenProjectCategory(CitizenProjectCategory $category): array
    {
        return $this
            ->createQueryBuilder('cpcs')
            ->leftJoin('cpcs.skill', 's')
            ->where('cpcs.category = :cpc')
            ->setParameter('cpc', $category)
            ->getQuery()
            ->getResult()
        ;
    }
}
