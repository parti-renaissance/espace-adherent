<?php

declare(strict_types=1);

namespace App\Admin\Extension;

use App\Entity\AdherentMessage\AdherentMessage;
use App\Entity\AdherentMessage\AdherentMessageFilter;
use App\Entity\Administrator;
use App\Entity\Committee;
use App\Entity\ZoneableEntityInterface;
use App\Security\Voter\Admin\ZoneableEntityVoter;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\QueryBuilder;
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

        /** @var QueryBuilder $queryBuilder */
        $queryBuilder = $query->getQueryBuilder();

        if (!is_a($resourceClass = $admin->getModelClass(), ZoneableEntityInterface::class, true)) {
            $queryBuilder->andWhere('1 = 0');

            return;
        }

        $rootAlias = $queryBuilder->getRootAliases()[0];
        $zones = $user->getZones()->toArray();
        $repository = $this->entityManager->getRepository($resourceClass);

        if (AdherentMessage::class === $resourceClass) {
            $queryBuilder
                ->innerJoin($rootAlias.'.filter', 'filter')
                ->leftJoin('filter.committee', 'publication_committee')
            ;

            $expr = $queryBuilder->expr();

            $condition = $expr
                ->orX(
                    $expr->exists($repository->createGeoZonesQueryBuilder(
                        'filter',
                        $zones,
                        $queryBuilder,
                        AdherentMessageFilter::class,
                        'admin_extension_filter_by_zones',
                        'zones',
                        'admin_extension_filter_by_zones_param',
                        null,
                        true,
                        'admin_extension_filter_by_zones_parents'
                    )->getDQL()),
                    $expr->exists($repository->createGeoZonesQueryBuilder(
                        'publication_committee',
                        $zones,
                        $queryBuilder,
                        Committee::class,
                        'admin_extension_filter_by_committee_zones',
                        'zones',
                        'admin_extension_filter_by_committee_zones_param',
                        null,
                        true,
                        'admin_extension_filter_by_committee_zones_parents'
                    )->getDQL()),
                )
            ;

            $queryBuilder->andWhere($condition);

            return;
        }

        $repository->withGeoZones(
            $zones,
            $queryBuilder,
            $rootAlias,
            $resourceClass,
            'admin_extension_filter_by_zones',
            $resourceClass::getZonesPropertyName(),
            'admin_extension_filter_by_zones_param',
            [$resourceClass, 'alterQueryBuilderForZones']
        );
    }

    private function getAdministrator(): Administrator
    {
        return $this->security->getUser();
    }
}
