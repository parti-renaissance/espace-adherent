<?php

namespace App\Repository\Procuration;

use App\Entity\ProcurationV2\Request;
use App\Repository\GeoZoneTrait;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

class RequestRepository extends ServiceEntityRepository
{
    use GeoZoneTrait;

    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Request::class);
    }
}
