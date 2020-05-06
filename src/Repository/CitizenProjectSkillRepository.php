<?php

namespace App\Repository;

use App\Entity\CitizenProjectSkill;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Symfony\Bridge\Doctrine\RegistryInterface;

class CitizenProjectSkillRepository extends ServiceEntityRepository
{
    public function __construct(RegistryInterface $registry)
    {
        parent::__construct($registry, CitizenProjectSkill::class);
    }

    public function findOneByName(string $name): ?CitizenProjectSkill
    {
        return $this->findOneBy(['name' => $name]);
    }
}
