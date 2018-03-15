<?php

namespace AppBundle\Repository;

use AppBundle\Entity\CitizenProjectSkill;
use Doctrine\ORM\EntityRepository;

class CitizenProjectSkillRepository extends EntityRepository
{
    public function findOneByName(string $name): ?CitizenProjectSkill
    {
        return $this->findOneBy(['name' => $name]);
    }

    public function findAllAsPartialArray(): array
    {
        return $this->createQueryBuilder('skill')
            ->select('skill.id, skill.name')
            ->getQuery()
            ->getArrayResult()
        ;
    }
}
