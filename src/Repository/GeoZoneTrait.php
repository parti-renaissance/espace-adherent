<?php

namespace App\Repository;

use App\Entity\Geo\Zone;
use Doctrine\ORM\QueryBuilder;

trait GeoZoneTrait
{
    public function withGeoZones(
        array $zones,
        QueryBuilder $queryBuilder,
        string $rootAlias,
        string $entityClass,
        string $entityClassAlias,
        string $zoneRelation,
        string $zoneRelationAlias,
        callable $queryModifier = null,
        bool $withParents = true,
        string $zoneParentAlias = 'zone_parent'
    ): QueryBuilder {
        if (!$zones) {
            return $queryBuilder;
        }

        $zoneQueryBuilder = $queryBuilder
            ->getEntityManager()
            ->createQueryBuilder()
            ->select($select = sprintf('%s.id', $entityClassAlias))
            ->from($entityClass, $entityClassAlias)
            ->innerJoin(sprintf('%s.%s', $entityClassAlias, $zoneRelation), $zoneRelationAlias)
            ->groupBy($select)
        ;

        $orX = $queryBuilder->expr()->orX();
        $orX->add(sprintf('%s IN (:zone_ids)', $zoneRelationAlias));

        $queryBuilder->setParameter('zone_ids', array_map(static function (Zone $zone): int {
            return $zone->getId();
        }, $zones));

        if ($withParents) {
            $parents = array_filter(array_map(static function (Zone $zone): ?int {
                return $zone->isCityGrouper() ? null : $zone->getId();
            }, $zones));

            if ($parents) {
                if (!\in_array($zoneParentAlias, $queryBuilder->getAllAliases(), true)) {
                    $zoneQueryBuilder->innerJoin($zoneRelationAlias.'.parents', $zoneParentAlias);
                }

                $orX->add(sprintf('%s IN (:zone_parent_ids)', $zoneParentAlias));
                $queryBuilder->setParameter('zone_parent_ids', $parents);
            }
        }

        $zoneQueryBuilder->where($orX);

        if ($queryModifier) {
            $queryModifier($zoneQueryBuilder, $entityClassAlias);
        }

        $queryBuilder->andWhere(sprintf('%s.id IN (%s)', $rootAlias, $zoneQueryBuilder->getDQL()));

        return $queryBuilder;
    }
}
