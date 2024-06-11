<?php

namespace App\Repository\Geo;

use App\Entity\Committee;
use App\Entity\DepartmentSite\DepartmentSite;
use App\Entity\Geo\City;
use App\Entity\Geo\GeoInterface;
use App\Entity\Geo\Region;
use App\Entity\Geo\Zone;
use App\Entity\Geo\ZoneableInterface;
use App\Entity\Geo\ZoneTagEnum;
use App\Entity\ReferentTag;
use App\Geo\Http\ZoneAutocompleteFilter;
use App\Repository\UuidEntityRepositoryTrait;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\Query;
use Doctrine\ORM\Query\Expr\Andx;
use Doctrine\ORM\Query\Expr\Join;
use Doctrine\ORM\Query\Expr\Orx;
use Doctrine\ORM\QueryBuilder;
use Doctrine\Persistence\ManagerRegistry;

class ZoneRepository extends ServiceEntityRepository
{
    use UuidEntityRepositoryTrait;

    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Zone::class);
    }

    public function zoneableAsZone(ZoneableInterface $zoneable): Zone
    {
        $zone = $this->findByZoneable($zoneable);

        if (!$zone) {
            $zone = new Zone($zoneable->getZoneType(), $zoneable->getCode(), $zoneable->getName());
        }

        if (\in_array($zoneable->getZoneType(), [Zone::CITY, Zone::BOROUGH], true)) {
            $zone->setPostalCode($zoneable->getPostalCode());
        }

        $zone->activate($zoneable->isActive());
        $zone->setName($zoneable->getName());
        $zone->setGeoData($zoneable->getGeoData());

        return $zone;
    }

    public function createSelectForCandidatesQueryBuilder(): QueryBuilder
    {
        return $this->createTypesConditionQueryBuilder(Zone::CANDIDATE_TYPES);
    }

    public function createSelectForJeMarcheNotificationsQueryBuilder(): QueryBuilder
    {
        return $this->createTypesConditionQueryBuilder([Zone::REGION, Zone::DEPARTMENT]);
    }

    private function createTypesConditionQueryBuilder(array $types): QueryBuilder
    {
        return $this->createQueryBuilder('zone')
            ->where('zone.type IN (:types)')
            ->setParameters([
                'types' => $types,
            ])
        ;
    }

    /**
     * @return Zone[]
     */
    public function searchByFilterInsideManagedZones(ZoneAutocompleteFilter $filter, array $zones, ?int $perType): array
    {
        if (null !== $perType && empty($filter->q) && false === $filter->searchEvenEmptyTerm) {
            return [];
        }

        $grouped = [];
        foreach ($filter->getTypes() as $type) {
            $grouped[] = $this->doSearchForFilter($filter, $zones, $type, $perType);
        }

        return array_merge(...$grouped);
    }

    private function doSearchForFilter(ZoneAutocompleteFilter $filter, array $zones, string $type, ?int $max): array
    {
        $qb = $this->createQueryBuilder('zone')
            ->andWhere('zone.type = :type')
            ->setParameter(':type', $type)
            ->orderBy('LENGTH(zone.code)')
            ->addOrderBy('zone.name')
        ;

        if ($filter->activeOnly) {
            $qb
                ->andWhere('zone.active = :active')
                ->setParameter(':active', true)
            ;
        }

        if ($zones) {
            $qb
                ->leftJoin('zone.parents', 'parents')
                ->andWhere(
                    $qb->expr()->orX(
                        $qb->expr()->in('zone.id', ':zones'),
                        $qb->expr()->in('parents.id', ':zones'),
                    )
                )
                ->setParameter(':zones', $zones)
            ;
        }

        if (!empty($term = $filter->q)) {
            $qb
                ->addSelect(<<<SQL
                        CASE
                            WHEN REPLACE(zone.name, '-', ' ') = :term_strict THEN 1
                            WHEN REPLACE(zone.name, '-', ' ') LIKE :term_starts_with THEN 2
                            ELSE 3
                        END AS HIDDEN score
                    SQL)
                ->orderBy('score')
                ->addOrderBy('zone.name')
                ->addOrderBy('LENGTH(zone.code)')
                ->andWhere(
                    $qb->expr()->orX(
                        $qb->expr()->like("REPLACE(zone.name, '-', ' ')", ':term_contains'),
                        $qb->expr()->like('zone.code', ':term_code'),
                        $qb->expr()->like('zone.postalCode', ':term_code'),
                    )
                )
                ->setParameter(':term_strict', str_replace('-', ' ', $term))
                ->setParameter(':term_starts_with', str_replace('-', ' ', $term).'%')
                ->setParameter(':term_contains', '%'.str_replace('-', ' ', $term).'%')
                ->setParameter(':term_code', "%$term%")
            ;
        }

        if ($filter->usedByCommittees || $filter->availableForCommittee) {
            $subQuery = $this->getEntityManager()->createQueryBuilder()
                ->select('DISTINCT committee_zone.id')
                ->from(Committee::class, 'committee')
                ->innerJoin('committee.zones', 'committee_zone')
                ->where('committee.version = 2')
            ;

            $qb
                ->andWhere(sprintf('zone.id %s (%s)', $filter->usedByCommittees ? 'IN' : 'NOT IN', $subQuery->getDQL()))
                ->andWhere('(zone.tags IS NULL OR FIND_IN_SET(:zone_tag_cc_multi_dpt, zone.tags) = 0)')
                ->setParameter('zone_tag_cc_multi_dpt', ZoneTagEnum::CITY_COMMUNITY_MULTI_DEPARTMENT)
            ;
        }

        return $qb
            ->getQuery()
            ->setMaxResults($max)
            ->getResult()
        ;
    }

    public function findForMandateAdminAutocomplete(?string $term, array $types, array $codes, int $limit): array
    {
        if (!$types) {
            return [];
        }

        $qb = $this->createQueryBuilder('zone');

        $qb
            ->andWhere($qb->expr()->in('zone.type', ':types'))
            ->setParameter(':types', $types)
            ->setMaxResults($limit)
        ;

        if ($term) {
            $qb
                ->andWhere(
                    $qb->expr()->orX(
                        $qb->expr()->like('zone.name', ':term'),
                        $qb->expr()->like('zone.code', ':term'),
                    )
                )
                ->setParameter(':term', "$term%")
            ;
        }

        if ($codes) {
            $qb
                ->andWhere($qb->expr()->in('zone.code', ':codes'))
                ->setParameter(':codes', $codes)
            ;
        }

        return $qb
            ->getQuery()
            ->getResult()
        ;
    }

    public function findForJecouteByReferentTags(array $referentTags): array
    {
        $qb = $this->createQueryBuilder('zone');

        return $qb
            ->leftJoin('zone.children', 'child')
            ->leftJoin(ReferentTag::class, 'tag', Join::WITH, 'zone = tag.zone')
            ->leftJoin(ReferentTag::class, 'child_tag', Join::WITH, 'child = child_tag.zone')
            ->where($qb->expr()->orX(
                'child.type = :country AND zone.type = :foreign_district',
                'zone.type = :department',
                'zone.type = :borough AND zone.name LIKE :paris',
                'zone.type = :region AND zone.name = :corse'
            ))
            ->andWhere('(tag IN (:tags) OR child_tag IN (:tags))')
            ->setParameters([
                'tags' => $referentTags,
                'borough' => Zone::BOROUGH,
                'department' => Zone::DEPARTMENT,
                'region' => Zone::REGION,
                'country' => Zone::COUNTRY,
                'foreign_district' => Zone::FOREIGN_DISTRICT,
                'paris' => 'Paris %',
                'corse' => 'Corse',
            ])
            ->getQuery()
            ->getResult()
        ;
    }

    public function isInJecouteZones(array $referentTags, Zone $zone): bool
    {
        $qb = $this->createQueryBuilder('zone');

        $zones = $qb
            ->select('COUNT(1)')
            ->leftJoin('zone.children', 'child', Join::WITH, 'child = :zone')
            ->leftJoin(ReferentTag::class, 'tag', Join::WITH, 'zone = tag.zone')
            ->leftJoin(ReferentTag::class, 'child_tag', Join::WITH, 'child = child_tag.zone')
            ->where($qb->expr()->orX(
                'child.type = :country AND zone.type = :foreign_district',
                'zone.type = :department AND zone.code != :paris_dpt',
                'zone.type = :borough AND zone.name LIKE :paris',
                'zone.type = :region AND zone.name = :corse'
            ))
            ->andWhere('(tag IN (:tags) OR child_tag IN (:tags))')
            ->andWhere('(zone = :zone OR child IS NOT NULL)')
            ->setParameters([
                'tags' => $referentTags,
                'borough' => Zone::BOROUGH,
                'department' => Zone::DEPARTMENT,
                'region' => Zone::REGION,
                'country' => Zone::COUNTRY,
                'foreign_district' => Zone::FOREIGN_DISTRICT,
                'paris' => 'Paris %',
                'paris_dpt' => '75',
                'corse' => 'Corse',
                'zone' => $zone,
            ])
            ->getQuery()
            ->getSingleResult()
        ;

        return \count($zones) > 0;
    }

    public function isInJecouteZonesWithParents(array $referentTags, Zone $zone): bool
    {
        $zones = $this->createQueryBuilder('zone')
            ->select('COUNT(1)')
            ->leftJoin('zone.children', 'child', Join::WITH, 'child = :zone')
            ->leftJoin('zone.parents', 'parent', Join::WITH, 'parent = :zone')
            ->leftJoin(ReferentTag::class, 'tag', Join::WITH, 'zone = tag.zone')
            ->leftJoin(ReferentTag::class, 'child_tag', Join::WITH, 'child = child_tag.zone')
            ->leftJoin(ReferentTag::class, 'parent_tag', Join::WITH, 'parent = parent_tag.zone')
            ->where('(tag IN (:tags) OR child_tag IN (:tags) OR parent_tag IN (:tags))')
            ->andWhere('(zone = :zone OR child IS NOT NULL)')
            ->setParameter('tags', $referentTags)
            ->setParameter('zone', $zone)
            ->getQuery()
            ->getSingleResult()
        ;

        return \count($zones) > 0;
    }

    public function isInZones(array $zones, array $parents): bool
    {
        if (!empty(array_intersect($zones, $parents))) {
            return true;
        }

        // If parent zone is a District (circonscription) we need to compare it with the same type
        // instead of matching by parent relation.
        $filterDistrictZoneCallback = function (Zone $zone) { return $zone->isDistrict(); };
        if ($districtParents = array_filter($parents, $filterDistrictZoneCallback)) {
            return !empty(array_intersect(array_filter($zones, $filterDistrictZoneCallback), $districtParents));
        }

        $count = (int) $this->createQueryBuilder('zone')
            ->select('COUNT(1)')
            ->innerJoin('zone.parents', 'parent')
            ->where('zone IN (:zones) AND parent IN (:parents)')
            ->setParameter('zones', $zones)
            ->setParameter('parents', $parents)
            ->getQuery()
            ->getSingleScalarResult()
        ;

        return $count > 0;
    }

    public function findByPostalCode(string $postalCode): array
    {
        $postalCode = str_pad($postalCode, 5, '0', \STR_PAD_LEFT);
        $dpt = substr($postalCode, 0, 2);
        if (\in_array($dpt, [97, 98])) {
            $dpt = substr($postalCode, 0, 3);
        }

        return $this->createQueryBuilder('zone')
            ->leftJoin('zone.parents', 'parent', Join::WITH, 'parent.type IN (:dpt_type, :city_type)')
            ->leftJoin('zone.children', 'child', Join::WITH, 'child.type = :city_type')
            ->where('zone.type IN (:city_type, :district_type)')
            ->andWhere(
                (new Orx())
                    ->add('parent.type = :dpt_type AND zone.type = :city_type AND parent.code = :dpt_code AND zone.postalCode LIKE :postal_code')
                    ->add((new Andx())
                        ->add('zone.type = :district_type')
                        ->add('(parent.type = :city_type AND parent.postalCode LIKE :postal_code) OR (child.postalCode LIKE :postal_code)')))
            ->setParameter('postal_code', '%'.$postalCode.'%')
            ->setParameter('dpt_code', $dpt)
            ->setParameter('dpt_type', Zone::DEPARTMENT)
            ->setParameter('city_type', Zone::CITY)
            ->setParameter('district_type', Zone::DISTRICT)
            ->getQuery()
            ->getResult()
        ;
    }

    public function findRegionByPostalCode(string $postalCode): ?Zone
    {
        return $this->createQueryBuilder('zone')
            ->leftJoin('zone.children', 'child')
            ->innerJoin(City::class, 'city', Join::WITH, 'child.code = city.code')
            ->where('(city.postalCode LIKE :postal_code_1 OR city.postalCode LIKE :postal_code_2)')
            ->andWhere('zone.type = :region AND child.type = :city')
            ->setParameters([
                'postal_code_1' => $postalCode.'%',
                'postal_code_2' => '%,'.$postalCode.'%',
                'region' => Zone::REGION,
                'city' => Zone::CITY,
            ])
            ->getQuery()
            ->setMaxResults(1)
            ->getOneOrNullResult()
        ;
    }

    /**
     * Finds zones by coordinates of the point.
     *
     * @return Zone[]
     */
    public function findByCoordinatesAndTypes(
        float $latitude,
        float $longitude,
        ?array $types,
        array $parents = []
    ): array {
        $qb = $this
            ->createQueryBuilder('zone')
            ->innerJoin('zone.geoData', 'geo_data')
            ->where("ST_Within(ST_GeomFromText(CONCAT('POINT(',:longitude,' ',:latitude,')')), geo_data.geoShape) = 1")
            ->setParameter('latitude', $latitude)
            ->setParameter('longitude', $longitude)
        ;

        if ($types) {
            $qb
                ->andWhere($qb->expr()->in('zone.type', ':types'))
                ->setParameter('types', $types)
            ;
        }

        if ($parents) {
            $parentIds = array_filter(array_map(static function (Zone $zone): ?int {
                return $zone->getId();
            }, $parents));

            $qb
                ->innerJoin('zone.parents', 'zone_parent')
                ->andWhere($qb->expr()->in('zone_parent.id', $parentIds))
            ;
        }

        return $qb
            ->getQuery()
            ->getResult()
        ;
    }

    public function findByZoneable(ZoneableInterface $zoneable): ?Zone
    {
        return $this->findOneBy([
            'code' => $zoneable->getCode(),
            'type' => $zoneable->getZoneType(),
        ]);
    }

    public function findOneByPostalCode(string $postalCode): ?Zone
    {
        return $this->createQueryBuilder('zone')
            ->where('zone.postalCode LIKE :postal_code_1 or zone.postalCode LIKE :postal_code_2')
            ->andWhere('zone.type IN (:zone_types)')
            ->setParameters([
                'postal_code_1' => $postalCode.'%',
                'postal_code_2' => '%,'.$postalCode.'%',
                'zone_types' => [Zone::CITY, Zone::BOROUGH],
            ])
            ->setMaxResults(1)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }

    public function findOneDepartmentByPostalCode(string $postalCode): ?Zone
    {
        return $this->createQueryBuilder('dpt_zone')
            ->innerJoin('dpt_zone.children', 'city_zone')
            ->andWhere('dpt_zone.type = :dpt_zone_type')
            ->andWhere('city_zone.type IN (:city_zone_type)')
            ->setParameter('dpt_zone_type', Zone::DEPARTMENT)
            ->setParameter('city_zone_type', [Zone::CITY, Zone::BOROUGH])
            ->andWhere('city_zone.postalCode LIKE :postal_code_1 or city_zone.postalCode LIKE :postal_code_2')
            ->setParameters([
                'postal_code_1' => $postalCode.'%',
                'postal_code_2' => '%,'.$postalCode.'%',
                'dpt_zone_type' => Zone::DEPARTMENT,
                'city_zone_type' => [Zone::CITY, Zone::BOROUGH],
            ])
            ->setMaxResults(1)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }

    public function findGeoZoneByGeoRegion(Region $region): ?Zone
    {
        return $this->createQueryBuilder('zone')
            ->innerJoin(Region::class, 'region', Join::WITH, 'zone.code = region.code')
            ->where('region.code = :code AND zone.type = :type')
            ->setParameters([
                'code' => $region->getCode(),
                'type' => Zone::REGION,
            ])
            ->setMaxResults(1)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }

    /** @return Zone[] */
    public function findByTag(string $tag): array
    {
        return $this->createQueryBuilder('zone')
            ->where('FIND_IN_SET(:tag, zone.tags) > 0')
            ->orderBy('zone.name')
            ->setParameter('tag', $tag)
            ->getQuery()
            ->getResult()
        ;
    }

    /** @return Zone[] */
    public function findParent(string $parentType, string $childCode, string $childType): array
    {
        return $this->createQueryBuilder('parent_zone')
            ->innerJoin('parent_zone.children', 'child_zone')
            ->where('child_zone.type = :child_zone_type')
            ->andWhere('child_zone.code = :child_zone_code')
            ->andWhere('parent_zone.type = :parent_zone_type')
            ->setParameters([
                'child_zone_type' => $childType,
                'child_zone_code' => $childCode,
                'parent_zone_type' => $parentType,
            ])
            ->getQuery()
            ->getResult()
        ;
    }

    public function getFrenchCities(): array
    {
        return $this->createFrenchCitiesQueryBuilder()
            ->select('zone.name')
            ->addSelect('zone.code AS insee_code', 'zone.postalCode AS postal_code')
            ->getQuery()
            ->getArrayResult()
        ;
    }

    public function createFrenchCitiesQueryBuilder(): QueryBuilder
    {
        return $this->createQueryBuilder('zone', 'zone.code')
            ->where('zone.type IN (:types)')
            ->andWhere('zone.code NOT IN (:codes)')
            ->setParameter('types', [Zone::BOROUGH, Zone::CITY])
            ->setParameter('codes', [GeoInterface::CITY_PARIS_CODE, GeoInterface::CITY_LYON_CODE, GeoInterface::CITY_MARSEILLE_CODE])
        ;
    }

    public function findByInseeCode(string $code): ?Zone
    {
        return $this->createQueryBuilder('zone', 'zone.code')
            ->where('zone.type IN (:types)')
            ->andWhere('zone.code = :code')
            ->setParameter('types', [Zone::BOROUGH, Zone::CITY])
            ->setParameter('code', $code)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }

    public function searchCitiesInZones(array $zones, string $search): array
    {
        $qb = $this->createFrenchCitiesQueryBuilder()
            ->select('PARTIAL zone.{id, name, code, postalCode}')
            ->innerJoin('zone.parents', 'parent')
            ->andWhere('zone IN (:zones) OR parent IN (:zones)')
            ->andWhere('(zone.name LIKE :name OR zone.postalCode LIKE :postal_code)')
            ->setParameter('name', $search.'%')
            ->setParameter('postal_code', '%'.$search.'%')
            ->setParameter('zones', $zones)
        ;

        return $qb->getQuery()->setHint(Query::HINT_FORCE_PARTIAL_LOAD, true)->getResult();
    }

    public function findOneByCode(string $code): ?Zone
    {
        return $this->findOneBy(['code' => $code, 'active' => true]);
    }

    public function findAllDepartmentSiteIndexByCode(): array
    {
        return $this->createQueryBuilder('zone', 'zone.code')
            ->select('zone.name', 'zone.code')
            ->addSelect('site.slug AS site_slug')
            ->leftJoin(DepartmentSite::class, 'site', Join::WITH, 'zone = site.zone')
            ->where('zone.type = :dpt OR (zone.type = :custom AND zone.code = :zone_fde)')
            ->orderBy('zone.name', 'ASC')
            ->setParameter('dpt', Zone::DEPARTMENT)
            ->setParameter('custom', Zone::CUSTOM)
            ->setParameter('zone_fde', Zone::FDE_CODE)
            ->getQuery()
            ->getArrayResult()
        ;
    }

    /**
     * @return Zone[]
     */
    public function getAllForAdherentsStats(): array
    {
        return $this->createQueryBuilder('zone')
            ->addSelect('parent')
            ->where('zone.type = :type_dpt OR zone.code = :code_fde')
            ->andWhere('zone.code NOT IN (:omitted_codes)')
            ->leftJoin('zone.parents', 'parent', Join::WITH, 'parent.type = :type_region')
            ->setParameters([
                'type_dpt' => Zone::DEPARTMENT,
                'type_region' => Zone::REGION,
                'code_fde' => Zone::FDE_CODE,
                'omitted_codes' => ['69M', '69D', '2A', '2B', '64B', '64PB'],
            ])
            ->getQuery()
            ->getResult()
        ;
    }

    /**
     * @return Zone[]
     */
    public function getAllForProcurationsStats(): array
    {
        return $this->createQueryBuilder('zone')
            ->addSelect('parent')
            ->where('zone.type IN (:zone_types)')
            ->andWhere('zone.code NOT IN (:omitted_codes)')
            ->leftJoin('zone.parents', 'parent', Join::WITH, 'parent.type = :type_region')
            ->setParameters([
                'zone_types' => [
                    Zone::DEPARTMENT,
                    Zone::COUNTRY,
                ],
                'type_region' => Zone::REGION,
                'omitted_codes' => ['69M', '69D', '2A', '2B', '64B', '64PB'],
            ])
            ->getQuery()
            ->getResult()
        ;
    }
}
