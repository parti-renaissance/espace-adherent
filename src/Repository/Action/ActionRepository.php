<?php

declare(strict_types=1);

namespace App\Repository\Action;

use App\Entity\Action\Action;
use App\Repository\NearbyTrait;
use App\Repository\UuidEntityRepositoryTrait;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

class ActionRepository extends ServiceEntityRepository
{
    use UuidEntityRepositoryTrait;
    use NearbyTrait;

    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Action::class);
    }
}
