<?php

namespace App\Repository\Geo;

use App\Entity\Geo\Borough;
use App\Entity\Geo\City;
use App\Entity\Geo\Department;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\Query\Expr\Join;
use Doctrine\Persistence\ManagerRegistry;

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
            ->leftJoin(City::class, 'city', Join::WITH, 'department = city.department')
            ->leftJoin(Borough::class, 'borough', Join::WITH, 'city = borough.city')
            ->andWhere('city.postalCode LIKE :postal_code OR borough.postalCode LIKE :postal_code')
            ->setParameter('postal_code', '%'.$postalCode.'%')
            ->innerJoin('department.region', 'region')
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
}
