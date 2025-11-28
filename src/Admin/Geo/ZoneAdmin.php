<?php

declare(strict_types=1);

namespace App\Admin\Geo;

use App\Entity\Geo\Zone;
use Sonata\AdminBundle\Admin\AbstractAdmin;
use Sonata\AdminBundle\Datagrid\DatagridMapper;
use Sonata\AdminBundle\Datagrid\ProxyQueryInterface;

class ZoneAdmin extends AbstractAdmin
{
    protected function configureDefaultSortValues(array &$sortValues): void
    {
        $sortValues['_sort_by'] = 'priority';
    }

    protected function configureDatagridFilters(DatagridMapper $filter): void
    {
        $filter
            ->add('name')
            ->add('code')
        ;
    }

    protected function configureQuery(ProxyQueryInterface $query): ProxyQueryInterface
    {
        $rootAlias = $query->getRootAliases()[0];

        $query
            ->addSelect("
                CASE
                    WHEN $rootAlias.type = :zone_country THEN 1
                    WHEN $rootAlias.type = :zone_region THEN 2
                    WHEN $rootAlias.type = :zone_department THEN 3
                    WHEN $rootAlias.type = :zone_circo THEN 4
                    WHEN $rootAlias.type = :zone_canton THEN 5
                    WHEN $rootAlias.type = :zone_city_community THEN 6
                    WHEN $rootAlias.type = :zone_city THEN 7
                    WHEN $rootAlias.type = :zone_borough THEN 8
                    ELSE 9
                END AS HIDDEN priority
            ")
            ->setParameter('zone_city', Zone::CITY)
            ->setParameter('zone_city_community', Zone::CITY_COMMUNITY)
            ->setParameter('zone_borough', Zone::BOROUGH)
            ->setParameter('zone_department', Zone::DEPARTMENT)
            ->setParameter('zone_circo', Zone::DISTRICT)
            ->setParameter('zone_canton', Zone::CANTON)
            ->setParameter('zone_region', Zone::REGION)
            ->setParameter('zone_country', Zone::COUNTRY)
        ;

        return $query;
    }
}
