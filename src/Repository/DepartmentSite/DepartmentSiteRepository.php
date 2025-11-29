<?php

declare(strict_types=1);

namespace App\Repository\DepartmentSite;

use App\Entity\DepartmentSite\DepartmentSite;
use App\Repository\UuidEntityRepositoryTrait;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

class DepartmentSiteRepository extends ServiceEntityRepository
{
    use UuidEntityRepositoryTrait;

    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, DepartmentSite::class);
    }
}
