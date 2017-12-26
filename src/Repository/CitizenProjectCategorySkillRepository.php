<?php

namespace AppBundle\Repository;

use AppBundle\Entity\CitizenProjectCategory;
use Doctrine\ORM\EntityRepository;

class CitizenProjectCategorySkillRepository extends EntityRepository
{
    public function findByCitizenProjectCategoryAndTerm(CitizenProjectCategory $category, string $term): array
    {
        return $this
            ->createQueryBuilder('cpcs')
            ->leftJoin('cpcs.skill', 's')
            ->where('cpcs.category = :cpc')
            ->andWhere('LOWER(s.name) LIKE :term')
            ->setParameter('cpc', $category)
            ->setParameter('term', '%'.strtolower($term).'%')
            ->getQuery()
            ->getResult();
    }
}
