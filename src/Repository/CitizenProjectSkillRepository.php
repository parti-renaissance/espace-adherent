<?php

namespace App\Repository;

use App\Entity\CitizenProjectSkill;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Common\Persistence\ManagerRegistry;

class CitizenProjectSkillRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, CitizenProjectSkill::class);
    }

    public function findOneByName(string $name): ?CitizenProjectSkill
    {
        return $this->findOneBy(['name' => $name]);
    }
}
