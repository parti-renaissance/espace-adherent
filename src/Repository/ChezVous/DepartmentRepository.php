<?php

namespace App\Repository\ChezVous;

use App\Entity\ChezVous\Department;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Symfony\Bridge\Doctrine\RegistryInterface;

class DepartmentRepository extends ServiceEntityRepository
{
    public function __construct(RegistryInterface $registry)
    {
        parent::__construct($registry, Department::class);
    }

    public function findOneByCode(string $code): ?Department
    {
        return $this->findOneBy(['code' => $code]);
    }
}
