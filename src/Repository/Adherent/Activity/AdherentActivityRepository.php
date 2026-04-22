<?php

declare(strict_types=1);

namespace App\Repository\Adherent\Activity;

use App\Entity\Adherent\Activity\AdherentActivity;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

class AdherentActivityRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, AdherentActivity::class);
    }
}
