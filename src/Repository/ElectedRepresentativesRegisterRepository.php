<?php

namespace AppBundle\Repository;

use AppBundle\Entity\ElectedRepresentativesRegister;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Common\Persistence\ManagerRegistry;

class ElectedRepresentativesRegisterRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, ElectedRepresentativesRegister::class);
    }
}
