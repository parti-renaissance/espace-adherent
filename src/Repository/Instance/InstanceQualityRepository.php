<?php

namespace App\Repository\Instance;

use App\Entity\Instance\InstanceQuality;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

class InstanceQualityRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, InstanceQuality::class);
    }
}
