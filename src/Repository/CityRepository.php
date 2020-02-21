<?php

namespace AppBundle\Repository;

use ApiPlatform\Core\DataProvider\PaginatorInterface;
use AppBundle\Entity\Adherent;
use AppBundle\Entity\City;
use AppBundle\Entity\MunicipalManagerRoleAssociation;
use AppBundle\MunicipalManager\Filter\AssociationCityFilter;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\Query\Expr\Join;
use Symfony\Bridge\Doctrine\RegistryInterface;

class CityRepository extends ServiceEntityRepository
{
    use GeoFilterTrait;
    use PaginatorTrait;

    private const ALIAS = 'city';

    public function __construct(RegistryInterface $registry)
    {
        parent::__construct($registry, City::class);
    }

    /**
     * @return City[]|PaginatorInterface
     */
    public function findAllForFilter(AssociationCityFilter $filter, int $page, int $limit = 30): PaginatorInterface
    {
        $qb = $this->createQueryBuilder(self::ALIAS);

        if ($tags = $filter->getTags()) {
            $this->applyGeoFilter($qb, $tags, self::ALIAS, 'country', 'inseeCode');
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

        if ($country = $filter->getCountry()) {
            $qb
                ->andWhere(self::ALIAS.'.country = :country')
                ->setParameter('country', $country)
            ;
        }

        $municipalManagerFirstName = $filter->getMunicipalManagerFirstName();
        $municipalManagerLastName = $filter->getMunicipalManagerLastName();
        $municipalManagerEmail = $filter->getMunicipalManagerEmail();

        if ($municipalManagerFirstName || $municipalManagerLastName || $municipalManagerEmail) {
            $qb
                ->leftJoin(
                    MunicipalManagerRoleAssociation::class,
                    'municipal_manager_role',
                    Join::WITH,
                    self::ALIAS.' = municipal_manager_role.city'
                )
                ->leftJoin(
                    Adherent::class,
                    'municipal_manager',
                    Join::WITH,
                    'municipal_manager.municipalManagerRole = municipal_manager_role'
                )
            ;

            if ($municipalManagerFirstName) {
                $qb
                    ->andWhere('municipal_manager.firstName LIKE :municipal_manager_first_name')
                    ->setParameter('municipal_manager_first_name', sprintf('%%%s%%', $municipalManagerFirstName))
                ;
            }

            if ($municipalManagerLastName) {
                $qb
                    ->andWhere('municipal_manager.lastName LIKE :municipal_manager_last_name')
                    ->setParameter('municipal_manager_last_name', sprintf('%%%s%%', $municipalManagerLastName))
                ;
            }

            if ($municipalManagerEmail) {
                $qb
                    ->andWhere('municipal_manager.emailAddress LIKE :municipal_manager_email')
                    ->setParameter('municipal_manager_email', sprintf('%%%s%%', $municipalManagerEmail))
                ;
            }
        }

        $qb->orderBy(self::ALIAS.'.inseeCode', 'ASC');

        return $this->configurePaginator($qb, $page, $limit);
    }
}
