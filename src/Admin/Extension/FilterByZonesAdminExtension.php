<?php

namespace App\Admin\Extension;

use App\Entity\Administrator;
use App\Entity\ZoneableEntityInterface;
use Doctrine\ORM\EntityManagerInterface;
use Sonata\AdminBundle\Admin\AbstractAdminExtension;
use Sonata\AdminBundle\Admin\AdminInterface;
use Sonata\AdminBundle\Datagrid\ProxyQueryInterface;
use Symfony\Bundle\SecurityBundle\Security;

class FilterByZonesAdminExtension extends AbstractAdminExtension
{
    public function __construct(
        private readonly Security $security,
        private readonly EntityManagerInterface $entityManager,
    ) {
    }

    public function configureQuery(AdminInterface $admin, ProxyQueryInterface $query): void
    {
        $user = $this->getAdministrator();

        if ($user->getZones()->isEmpty()) {
            return;
        }

        if (!is_a($resourceClass = $admin->getModelClass(), ZoneableEntityInterface::class, true)) {
            $query->andWhere('1 = 0');

            return;
        }

        $alias = $query->getRootAliases()[0];

        $zones = $user->getZones()->toArray();

        $this
            ->entityManager
            ->getRepository($resourceClass)
            ->withGeoZones(
                $zones,
                $query->getQueryBuilder(),
                $alias,
                $resourceClass,
                'api_zone_filter_resource_alias',
                $resourceClass::getZonesPropertyName(),
                'api_zone_filter_zone_alias',
                [$resourceClass, 'alterQueryBuilderForZones']
            )
        ;
    }

    private function getAdministrator(): Administrator
    {
        return $this->security->getUser();
    }
}
