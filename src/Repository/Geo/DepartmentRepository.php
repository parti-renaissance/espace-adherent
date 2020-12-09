<?php

namespace App\Repository\Geo;

use App\Entity\Geo\City;
use App\Entity\Geo\Department;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Common\Persistence\ManagerRegistry;
use Doctrine\ORM\Query\Expr\Join;

final class DepartmentRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Department::class);
    }

    /**
     * @return Department[]
     */
    public function findAllGroupedByCode(): array
    {
        return $this->createQueryBuilder('d', 'd.code')->getQuery()->getResult();
    }

    public function findOneForJecoute(string $postalCode): ?Department
    {
        return $this
            ->createQueryBuilder('department')
            ->innerJoin(City::class, 'city', Join::WITH, 'department = city.department')
            ->andWhere('city.postalCode LIKE :postal_code')
            ->setParameter('postal_code', '%'.$postalCode.'%')
            ->innerJoin('department.region', 'region')
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
}
