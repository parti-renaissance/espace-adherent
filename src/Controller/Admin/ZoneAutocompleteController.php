<?php

declare(strict_types=1);

namespace App\Controller\Admin;

use App\Adherent\Authorization\ZoneBasedRoleTypeEnum;
use App\Adherent\MandateTypeEnum;
use App\Entity\Geo\Zone;
use App\Repository\Geo\ZoneRepository;
use Doctrine\ORM\QueryBuilder;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Symfony\Contracts\Translation\TranslatorInterface;

#[IsGranted('ROLE_ADMIN_DASHBOARD')]
class ZoneAutocompleteController
{
    public const ROUTE_NAME = 'admin_zone_autocomplete';

    public const PRESET_JECOUTE_MANAGED_AREA = 'jecoute_managed_area';
    public const PRESET_DEPARTMENT_SITE = 'department_site';
    public const PRESETS = [
        self::PRESET_JECOUTE_MANAGED_AREA,
        self::PRESET_DEPARTMENT_SITE,
    ];

    #[Route(path: '/autocomplete/zone', name: self::ROUTE_NAME, methods: ['GET'])]
    public function __invoke(
        Request $request,
        ZoneRepository $repository,
        TranslatorInterface $translator,
    ): JsonResponse {
        $searchText = trim((string) $request->query->get('q', ''));

        if ('' === $searchText) {
            return new JsonResponse(['status' => 'OK', 'more' => false, 'items' => []]);
        }

        $perPage = max(1, min(100, $request->query->getInt('_per_page', 25)));
        $page = max(1, $request->query->getInt('_page', 1));

        $qb = $repository->createQueryBuilder('zone')
            ->andWhere('zone.active = true')
            ->andWhere('zone.name LIKE :search OR zone.code LIKE :search OR zone.postalCode LIKE :search')
            ->setParameter('search', '%'.$searchText.'%')
            ->addSelect('
                CASE
                    WHEN zone.type = :t_country THEN 1
                    WHEN zone.type = :t_region THEN 2
                    WHEN zone.type = :t_department THEN 3
                    WHEN zone.type = :t_district THEN 4
                    WHEN zone.type = :t_canton THEN 5
                    WHEN zone.type = :t_city_community THEN 6
                    WHEN zone.type = :t_city THEN 7
                    WHEN zone.type = :t_borough THEN 8
                    ELSE 9
                END AS HIDDEN priority
            ')
            ->setParameter('t_country', Zone::COUNTRY)
            ->setParameter('t_region', Zone::REGION)
            ->setParameter('t_department', Zone::DEPARTMENT)
            ->setParameter('t_district', Zone::DISTRICT)
            ->setParameter('t_canton', Zone::CANTON)
            ->setParameter('t_city_community', Zone::CITY_COMMUNITY)
            ->setParameter('t_city', Zone::CITY)
            ->setParameter('t_borough', Zone::BOROUGH)
            ->orderBy('priority', 'ASC')
            ->addOrderBy('zone.name', 'ASC')
        ;

        $this->applyTypesFilter($qb, array_filter(explode(',', (string) $request->query->get('zone_types', ''))));
        $this->applyMandateTypeFilter($qb, $request->query->get('mandate_type'));
        $this->applyRoleTypeFilter($qb, $request->query->get('role_type'));
        $this->applyPreset($qb, $request->query->get('preset'));

        $qb->setFirstResult(($page - 1) * $perPage)->setMaxResults($perPage + 1);

        $results = $qb->getQuery()->getResult();
        $more = \count($results) > $perPage;
        $results = \array_slice($results, 0, $perPage);

        $items = array_map(fn (Zone $zone): array => [
            'id' => $zone->getId(),
            'label' => \sprintf(
                '%s : %s (%s)',
                $translator->trans('geo_zone.'.$zone->getType()),
                $zone->getName(),
                $zone->getCode()
            ),
        ], $results);

        return new JsonResponse(['status' => 'OK', 'more' => $more, 'items' => $items]);
    }

    private function applyTypesFilter(QueryBuilder $qb, array $types): void
    {
        $types = array_values(array_filter(array_map('strval', $types), static fn (string $type): bool => \in_array($type, Zone::TYPES, true)));

        if (!$types) {
            return;
        }

        $qb
            ->andWhere('zone.type IN (:filter_types)')
            ->setParameter('filter_types', $types)
        ;
    }

    private function applyMandateTypeFilter(QueryBuilder $qb, ?string $mandateType): void
    {
        if (!$mandateType) {
            return;
        }

        $zoneTypeConditions = MandateTypeEnum::ZONE_FILTER_BY_MANDATE[$mandateType] ?? null;

        if (!$zoneTypeConditions) {
            return;
        }

        $conditions = [];

        if (\array_key_exists('types', $zoneTypeConditions)) {
            $conditions[] = 'zone.type IN (:mandate_zone_types)';
            $qb->setParameter('mandate_zone_types', $zoneTypeConditions['types']);
        }

        if (\array_key_exists('codes', $zoneTypeConditions)) {
            $conditions[] = 'zone.code IN (:mandate_zone_codes)';
            $qb->setParameter('mandate_zone_codes', $zoneTypeConditions['codes']);
        }

        if ($conditions) {
            $qb->andWhere('('.implode(' AND ', $conditions).')');
        }
    }

    private function applyPreset(QueryBuilder $qb, ?string $preset): void
    {
        match ($preset) {
            self::PRESET_JECOUTE_MANAGED_AREA => $this->applyJecouteManagedAreaPreset($qb),
            self::PRESET_DEPARTMENT_SITE => $this->applyDepartmentSitePreset($qb),
            default => null,
        };
    }

    private function applyJecouteManagedAreaPreset(QueryBuilder $qb): void
    {
        $qb
            ->andWhere($qb->expr()->orX(
                'zone.type IN (:preset_jma_types)',
                'zone.type = :preset_jma_borough AND zone.name LIKE :preset_jma_paris',
                'zone.type = :preset_jma_region AND zone.name = :preset_jma_corse'
            ))
            ->setParameter('preset_jma_types', [Zone::DEPARTMENT, Zone::FOREIGN_DISTRICT])
            ->setParameter('preset_jma_borough', Zone::BOROUGH)
            ->setParameter('preset_jma_region', Zone::REGION)
            ->setParameter('preset_jma_paris', 'Paris %')
            ->setParameter('preset_jma_corse', 'Corse')
        ;
    }

    private function applyDepartmentSitePreset(QueryBuilder $qb): void
    {
        $qb
            ->andWhere($qb->expr()->orX(
                'zone.type = :preset_ds_department',
                'zone.type = :preset_ds_custom AND zone.code = :preset_ds_fde'
            ))
            ->setParameter('preset_ds_department', Zone::DEPARTMENT)
            ->setParameter('preset_ds_custom', Zone::CUSTOM)
            ->setParameter('preset_ds_fde', Zone::FDE_CODE)
        ;
    }

    private function applyRoleTypeFilter(QueryBuilder $qb, ?string $roleType): void
    {
        if (!$roleType) {
            return;
        }

        $zoneTypeConditions = ZoneBasedRoleTypeEnum::ZONE_TYPE_CONDITIONS[$roleType] ?? null;

        if (!$zoneTypeConditions) {
            return;
        }

        $expressions = [];
        $index = 0;

        foreach ($zoneTypeConditions as $key => $customCode) {
            if (is_numeric($key)) {
                $expressions[] = \sprintf('zone.type = :role_type_%d', $index);
                $qb->setParameter('role_type_'.$index, $customCode);
            } else {
                $expressions[] = \sprintf('(zone.type = :role_type_%1$d AND zone.code IN (:role_code_%1$d))', $index);
                $qb->setParameter('role_type_'.$index, $key);
                $qb->setParameter('role_code_'.$index, $customCode);
            }
            ++$index;
        }

        if ($expressions) {
            $qb->andWhere('('.implode(' OR ', $expressions).')');
        }
    }
}
