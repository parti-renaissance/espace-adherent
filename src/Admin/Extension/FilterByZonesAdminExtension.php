<?php

namespace App\Admin\Extension;

use App\Entity\Administrator;
use App\Entity\ZoneableEntityInterface;
use App\Security\Voter\Admin\ZoneableEntityVoter;
use Doctrine\ORM\EntityManagerInterface;
use Sonata\AdminBundle\Admin\AbstractAdminExtension;
use Sonata\AdminBundle\Admin\AdminInterface;
use Sonata\AdminBundle\Datagrid\ProxyQueryInterface;
use Sonata\AdminBundle\Security\Acl\Permission\AdminPermissionMap;
use Symfony\Bundle\SecurityBundle\Security;

class FilterByZonesAdminExtension extends AbstractAdminExtension
{
    public function __construct(
        private readonly Security $security,
        private readonly EntityManagerInterface $entityManager,
    ) {
    }

    public function getAccessMapping(AdminInterface $admin): array
    {
        return [
            'edit' => [ZoneableEntityVoter::ROLE_ADMIN_OBJECT_IN_USER_ZONES, AdminPermissionMap::PERMISSION_EDIT],
            'show' => [ZoneableEntityVoter::ROLE_ADMIN_OBJECT_IN_USER_ZONES, AdminPermissionMap::PERMISSION_VIEW],
            'delete' => [ZoneableEntityVoter::ROLE_ADMIN_OBJECT_IN_USER_ZONES, AdminPermissionMap::PERMISSION_DELETE],
        ];
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
                'admin_extension_filter_by_zones',
                $resourceClass::getZonesPropertyName(),
                'admin_extension_filter_by_zones_param',
                [$resourceClass, 'alterQueryBuilderForZones']
            )
        ;
    }

    private function getAdministrator(): Administrator
    {
        return $this->security->getUser();
    }
}
