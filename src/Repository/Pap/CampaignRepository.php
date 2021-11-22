<?php

namespace App\Repository\Pap;

use App\Entity\Pap\Campaign;
use App\Repository\UuidEntityRepositoryTrait;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

class CampaignRepository extends ServiceEntityRepository
{
    use UuidEntityRepositoryTrait;

    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Campaign::class);
    }
}
