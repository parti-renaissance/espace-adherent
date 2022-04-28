<?php

namespace App\Repository\Geo;

use App\Entity\Geo\City;
use App\Entity\Geo\Region;
use App\Entity\Geo\Zone;
use App\Entity\Geo\ZoneableInterface;
use App\Entity\ReferentTag;
use App\Repository\UuidEntityRepositoryTrait;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\Query\Expr\Join;
use Doctrine\ORM\Query\Expr\Orx;
use Doctrine\ORM\QueryBuilder;
use Doctrine\ORM\Tools\Pagination\Paginator;
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
    public function searchByTermAndManagedZonesGroupedByType(
        string $term,
        array $zones,
        array $types,
        bool $activeOnly,
        int $perType
    ): array {
        if ('' === $term) {
            return [];
        }

        $grouped = [];
        foreach ($types as $type) {
            $grouped[] = $this->doSearchByTermManagedZonesAndType($term, $zones, $type, $activeOnly, $perType);
        }

        return array_merge(...$grouped);
    }

    private function doSearchByTermManagedZonesAndType(
        string $term,
        array $zones,
        string $type,
        bool $activeOnly,
        int $max
    ): array {
        $qb = $this->createQueryBuilder('zone');
        $qb
            ->andWhere($qb->expr()->eq('zone.type', ':type'))
            ->setParameter(':type', $type)
        ;

        if ($activeOnly) {
            $qb
                ->andWhere($qb->expr()->eq('zone.active', ':active'))
                ->setParameter(':active', $activeOnly)
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
                ->orderBy('zone.name')
            ;
        }

        if ('' !== $term) {
            $qb
                ->andWhere(
                    $qb->expr()->orX(
                        $qb->expr()->like("REPLACE(zone.name, '-', ' ')", ':term_name'),
                        $qb->expr()->like('zone.code', ':term_code'),
                    )
                )
                ->setParameter(':term_name', '%'.str_replace('-', ' ', $term).'%')
                ->setParameter(':term_code', "%$term%")
            ;
        }

        $query = $qb
            ->getQuery()
            ->setFirstResult(0)
            ->setMaxResults($max)
        ;

        $paginator = new Paginator($query, true);

        return iterator_to_array($paginator->getIterator());
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
            ->innerJoin(City::class, 'city', Join::WITH, 'zone.code = city.code')
            ->leftJoin('zone.parents', 'parent')
            ->where('(city.postalCode LIKE :postal_code_1 OR city.postalCode LIKE :postal_code_2)')
            ->andWhere('parent.type = :dpt_type AND parent.code = :dpt_code AND zone.type = :city')
            ->setParameter('postal_code_1', $postalCode.'%')
            ->setParameter('postal_code_2', '%,'.$postalCode.'%')
            ->setParameter('dpt_code', $dpt)
            ->setParameter('dpt_type', Zone::DEPARTMENT)
            ->setParameter('city', Zone::CITY)
            ->getQuery()
            ->getResult()
        ;
    }

    public function findDistrictsByPostalCode(string $postalCode): array
    {
        $postalCode = str_pad($postalCode, 5, '0', \STR_PAD_LEFT);

        return $this->createQueryBuilder('zone')
            ->leftJoin('zone.children', 'child', Join::WITH, 'child.type = :city')
            ->leftJoin('zone.parents', 'parent', Join::WITH, 'parent.type = :city')
            ->where((new Orx())
                ->add('parent.postalCode LIKE :postal_code_1 OR parent.postalCode LIKE :postal_code_2')
                ->add('child.postalCode LIKE :postal_code_1 OR child.postalCode LIKE :postal_code_2'))
            ->andWhere('zone.type = :district')
            ->setParameter('postal_code_1', $postalCode.'%')
            ->setParameter('postal_code_2', '%,'.$postalCode.'%')
            ->setParameter('district', Zone::DISTRICT)
            ->setParameter('city', Zone::CITY)
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
}
