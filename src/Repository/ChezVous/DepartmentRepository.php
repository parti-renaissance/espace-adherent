<?php

namespace App\Repository\ChezVous;

use App\Entity\ChezVous\Department;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

class DepartmentRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Department::class);
    }

    public function findOneByCode(string $code): ?Department
    {
        return $this->findOneBy(['code' => $code]);
    }
}
