<?php

namespace App\Repository\Renaissance\Adhesion;

use App\Entity\Renaissance\Adhesion\AdherentRequest;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

class AdherentRequestRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, AdherentRequest::class);
    }
}
