<?php

namespace App\Repository\Geo;

use App\Entity\Geo\Department;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Symfony\Bridge\Doctrine\RegistryInterface;

final class DepartmentRepository extends ServiceEntityRepository
{
    public function __construct(RegistryInterface $registry)
    {
        parent::__construct($registry, Department::class);
    }

    /**
     * @return array<string, Department>
     */
    public function findAllGroupedByCode(): array
    {
        return $this->createQueryBuilder('d', 'd.code')->getQuery()->getResult();
    }
}
