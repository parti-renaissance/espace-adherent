<?php

declare(strict_types=1);

namespace App\Repository\Geo;

use App\Entity\Geo\City;
use App\Entity\Geo\Department;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\Query\Expr\Join;
use Doctrine\Persistence\ManagerRegistry;
use Psr\Log\LoggerAwareInterface;
use Psr\Log\LoggerAwareTrait;

/**
 * @extends \Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository<\App\Entity\Geo\Department>
 */
final class DepartmentRepository extends ServiceEntityRepository implements LoggerAwareInterface
{
    use LoggerAwareTrait;

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
        $departments = $this
            ->createQueryBuilder('department')
            ->innerJoin(City::class, 'city', Join::WITH, 'department = city.department')
            ->andWhere('city.postalCode LIKE :postal_code')
            ->setParameter('postal_code', '%'.$postalCode.'%')
            ->innerJoin('department.region', 'region')
            // Let's just see if we have more than one result to log an error
            ->setMaxResults(2)
            ->getQuery()
            ->getResult()
        ;

        if (\count($departments) > 1) {
            $this->logger->error(\sprintf('Found more than one department for postalCode "%s".', $postalCode));
        }

        return $departments[0] ?? null;
    }
}
