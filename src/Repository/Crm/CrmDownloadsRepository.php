<?php

namespace App\Repository\Crm;

use App\Entity\Crm\CrmDownloads;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

class CrmDownloadsRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, CrmDownloads::class);
    }
}
