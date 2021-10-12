<?php

namespace App\Repository\Geo;

use App\Entity\Geo\City;
use App\Entity\Geo\Department;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\Query\Expr\Join;
use Doctrine\Persistence\ManagerRegistry;
use Psr\Log\LoggerInterface;

final class DepartmentRepository extends ServiceEntityRepository
{
    private LoggerInterface $logger;

    public function __construct(ManagerRegistry $registry, LoggerInterface $logger)
    {
        parent::__construct($registry, Department::class);

        $this->logger = $logger;
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
        $queryBuilder = $this
            ->createQueryBuilder('department')
            ->innerJoin(City::class, 'city', Join::WITH, 'department = city.department')
            ->andWhere('city.postalCode LIKE :postal_code')
            ->setParameter('postal_code', '%'.$postalCode.'%')
            ->innerJoin('department.region', 'region')
        ;

        $count = (clone $queryBuilder)
            ->select('COUNT(DISTINCT(department))')
            ->getQuery()
            ->getSingleScalarResult()
        ;

        if ($count > 1) {
            $this->logger->error(sprintf('Found more than one department for postalCode "%s".', $postalCode));
        }

        return $queryBuilder
            ->setMaxResults(1)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
}
