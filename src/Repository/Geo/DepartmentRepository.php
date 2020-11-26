<?php

namespace App\Repository\Geo;

use App\Entity\Geo\City;
use App\Entity\Geo\Department;
use App\Entity\Jecoute\Region;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\Query\Expr\Join;
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

    public function findOneForJecoute(string $postalCode): ?Department
    {
        return $this
            ->createQueryBuilder('department')
            ->innerJoin(City::class, 'city', Join::WITH, 'department = city.department')
            ->andWhere('city.postalCode LIKE :postal_code')
            ->setParameter('postal_code', '%'.$postalCode.'%')
            ->innerJoin('department.region', 'region')
            ->innerJoin(Region::class, 'jecoute_region', Join::WITH, 'region = jecoute_region.geoRegion')
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
}
