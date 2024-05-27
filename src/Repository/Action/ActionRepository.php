<?php

namespace App\Repository\Action;

use App\Entity\Action\Action;
use App\Repository\NearbyTrait;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

class ActionRepository extends ServiceEntityRepository
{
    use NearbyTrait;

    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Action::class);
    }
}
