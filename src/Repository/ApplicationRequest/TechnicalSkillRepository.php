<?php

namespace AppBundle\Repository\ApplicationRequest;

use AppBundle\Entity\ApplicationRequest\TechnicalSkill;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\QueryBuilder;
use Symfony\Bridge\Doctrine\RegistryInterface;

class TechnicalSkillRepository extends ServiceEntityRepository
{
    public function __construct(RegistryInterface $registry)
    {
        parent::__construct($registry, TechnicalSkill::class);
    }

    public function createDisplayabledQueryBuilder(): QueryBuilder
    {
        return $this
            ->createQueryBuilder('t')
            ->andWhere('t.display = :display')
            ->setParameter('display', true)
            ->orderBy('t.name', 'ASC')
        ;
    }
}
