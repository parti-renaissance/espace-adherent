<?php

namespace App\Repository;

use ApiPlatform\State\Pagination\PaginatorInterface;
use App\AssociationCity\Filter\AssociationCityFilter;
use App\Entity\City;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

class CityRepository extends ServiceEntityRepository
{
    use PaginatorTrait;

    private const ALIAS = 'city';

    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, City::class);
    }

    /**
     * @return City[]|PaginatorInterface
     */
    public function findAllForFilter(AssociationCityFilter $filter, int $page, int $limit = 30): PaginatorInterface
    {
        $qb = $this
            ->createQueryBuilder(self::ALIAS)
            ->innerJoin(self::ALIAS.'.department', 'department')
            ->innerJoin('department.region', 'region')
        ;

        if ($managedInseeCode = $filter->getManagedInseeCode()) {
            $qb
                ->andWhere(self::ALIAS.'.inseeCode = :managed_insee_code')
                ->setParameter('managed_insee_code', $managedInseeCode)
            ;
        }

        if ($name = $filter->getName()) {
            $qb
                ->andWhere(self::ALIAS.'.name LIKE :name')
                ->setParameter('name', sprintf('%%%s%%', $name))
            ;
        }

        if ($inseeCode = $filter->getInseeCode()) {
            $qb
                ->andWhere(self::ALIAS.'.inseeCode LIKE :insee_code')
                ->setParameter('insee_code', sprintf('%s%%', $inseeCode))
            ;
        }

        $qb->orderBy(self::ALIAS.'.inseeCode', 'ASC');

        return $this->configurePaginator($qb, $page, $limit);
    }

    public function findByInseeCode(string $inseeCode): ?City
    {
        return $this
            ->createQueryBuilder('c')
            ->where('c.inseeCode = :insee_code')
            ->setParameter('insee_code', $inseeCode)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
}
