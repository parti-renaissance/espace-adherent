<?php

namespace App\Repository;

use App\Entity\Geo\Zone;
use Doctrine\ORM\QueryBuilder;

trait GeoZoneTrait
{
    public function withGeoZones(
        QueryBuilder $queryBuilder,
        array $zones,
        string $zoneAlias = 'zone',
        bool $withParents = true,
        string $zoneParentAlias = 'zone_parent'
    ): QueryBuilder {
        if (!$zones) {
            return $queryBuilder;
        }

        $orX = $queryBuilder->expr()->orX();
        $orX->add($queryBuilder->expr()->in($zoneAlias, array_map(static function (Zone $zone): int {
            return $zone->getId();
        }, $zones)));

        if ($withParents) {
            $parents = array_filter(array_map(static function (Zone $zone): ?int {
                return $zone->isCityGrouper() ? null : $zone->getId();
            }, $zones));

            if ($parents) {
                if (!\in_array($zoneParentAlias, $queryBuilder->getAllAliases(), true)) {
                    $queryBuilder->innerJoin($zoneAlias.'.parents', $zoneParentAlias);
                }

                $orX->add($queryBuilder->expr()->in($zoneParentAlias, $parents));
            }
        }

        return $queryBuilder->andWhere($orX);
    }
}
