<?php

namespace AppBundle\Repository\Formation;

use AppBundle\Entity\Formation\Module;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Symfony\Bridge\Doctrine\RegistryInterface;

class ModuleRepository extends ServiceEntityRepository
{
    public function __construct(RegistryInterface $registry)
    {
        parent::__construct($registry, Module::class);
    }
}
