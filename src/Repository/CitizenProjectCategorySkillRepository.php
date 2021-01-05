<?php

namespace App\Repository;

use App\Entity\CitizenProjectCategory;
use App\Entity\CitizenProjectCategorySkill;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

class CitizenProjectCategorySkillRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, CitizenProjectCategorySkill::class);
    }

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
