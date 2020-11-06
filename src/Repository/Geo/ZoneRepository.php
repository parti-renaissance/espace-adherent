<?php

namespace App\Repository\Geo;

use App\Entity\Geo\Zone;
use App\Entity\Geo\ZoneableInterface;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Common\Persistence\ManagerRegistry;
use Doctrine\ORM\QueryBuilder;
use Doctrine\ORM\Tools\Pagination\Paginator;

class ZoneRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Zone::class);
    }

    public function zoneableAsZone(ZoneableInterface $zoneable): Zone
    {
        $zone = $this->findOneBy([
            'code' => $zoneable->getCode(),
            'type' => $zoneable->getZoneType(),
        ]);

        if (!$zone) {
            $zone = new Zone($zoneable->getZoneType(), $zoneable->getCode(), $zoneable->getName());
        }

        $zone->activate($zoneable->isActive());
        $zone->setName($zoneable->getName());

        return $zone;
    }

    public function createSelectForCandidatesQueryBuilder(): QueryBuilder
    {
        return $this->createQueryBuilder('zone')
            ->where('zone.type IN (:types)')
            ->setParameters([
                'types' => Zone::CANDIDATE_TYPES,
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
        int $perType
    ): array {
        if ('' === $term) {
            return [];
        }

        $grouped = [];
        foreach ($types as $type) {
            $grouped[] = $this->doSearchByTermManagedZonesAndType($term, $zones, $type, $perType);
        }

        return array_merge(...$grouped);
    }

    private function doSearchByTermManagedZonesAndType(string $term, array $zones, string $type, int $max): array
    {
        $qb = $this->createQueryBuilder('zone');
        $qb
            ->andWhere($qb->expr()->eq('zone.type', ':type'))
            ->setParameter(':type', $type)
        ;

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
                        $qb->expr()->like('zone.name', ':term'),
                        $qb->expr()->like('zone.code', ':term'),
                    )
                )
                ->setParameter(':term', "%$term%")
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
}
