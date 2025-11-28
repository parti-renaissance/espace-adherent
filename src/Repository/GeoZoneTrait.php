<?php

declare(strict_types=1);

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
        ?callable $queryModifier = null,
        bool $withParents = true,
        string $zoneParentAlias = 'zone_parent',
    ): QueryBuilder {
        if (!$zones) {
            return $queryBuilder;
        }

        $zoneQueryBuilder = $this->createGeoZonesQueryBuilder(
            $rootAlias,
            $zones,
            $queryBuilder,
            $entityClass,
            $entityClassAlias,
            $zoneRelation,
            $zoneRelationAlias,
            $queryModifier,
            $withParents,
            $zoneParentAlias,
        );

        $queryBuilder->andWhere($queryBuilder->expr()->exists($zoneQueryBuilder->getDQL()));

        return $queryBuilder;
    }

    public function createGeoZonesQueryBuilder(
        string $rootAlias,
        array $zones,
        QueryBuilder $queryBuilder,
        string $entityClass,
        string $entityClassAlias,
        string $zoneRelation,
        string $zoneRelationAlias,
        ?callable $queryModifier = null,
        bool $withParents = true,
        string $zoneParentAlias = 'zone_parent',
    ): QueryBuilder {
        if (!$zones) {
            return $queryBuilder;
        }

        $zoneQueryBuilder = $queryBuilder
            ->getEntityManager()
            ->createQueryBuilder()
            ->select('1')
            ->from($entityClass, $entityClassAlias)
        ;

        if ($queryModifier) {
            $queryModifier($zoneQueryBuilder, $entityClassAlias);
        }

        $zoneQueryBuilder->innerJoin(
            str_contains($zoneRelation, '.') ? $zoneRelation : \sprintf('%s.%s', $entityClassAlias, $zoneRelation),
            $zoneRelationAlias
        );

        $orX = $queryBuilder->expr()->orX();
        $orX->add(\sprintf('%s IN (:%s_zone_ids)', $zoneRelationAlias, $zoneRelationAlias));

        $queryBuilder->setParameter(
            \sprintf('%s_zone_ids', $zoneRelationAlias),
            array_map(static fn (Zone $zone) => $zone->getId(), $zones)
        );

        if ($withParents) {
            $parents = array_filter(array_map(static fn (Zone $zone) => $zone->isCityGrouper() ? null : $zone->getId(), $zones));

            if ($parents) {
                if (!\in_array($zoneParentAlias, $queryBuilder->getAllAliases(), true)) {
                    $zoneQueryBuilder->leftJoin($zoneRelationAlias.'.parents', $zoneParentAlias);
                }

                $orX->add(\sprintf('%s IN (:%s_zone_parent_ids)', $zoneParentAlias, $zoneRelationAlias));
                $queryBuilder->setParameter(\sprintf('%s_zone_parent_ids', $zoneRelationAlias), $parents);
            }
        }

        return $zoneQueryBuilder
            ->andWhere($entityClassAlias.'.id = '.$rootAlias.'.id')
            ->andWhere($orX)
        ;
    }

    public function createEntityInGeoZonesQueryBuilder(
        array $zones,
        string $entityClass,
        string $entityClassAlias,
        string $zoneRelation,
        string $zoneRelationAlias,
        bool $withParents = true,
        string $zoneParentAlias = 'zone_parent',
    ): QueryBuilder {
        $zoneQueryBuilder = $this
            ->getEntityManager()
            ->createQueryBuilder()
            ->from($entityClass, $entityClassAlias)
            ->select(\sprintf('DISTINCT %s.id', $entityClassAlias))
            ->innerJoin(\sprintf('%s.%s', $entityClassAlias, $zoneRelation), $zoneRelationAlias)
        ;

        $orX = $zoneQueryBuilder->expr()->orX();
        $orX->add(\sprintf('%s IN (:zone_ids)', $zoneRelationAlias));

        $zoneQueryBuilder->setParameter('zone_ids', array_map(static fn (Zone $zone) => $zone->getId(), $zones));

        if ($withParents) {
            $parents = array_filter(array_map(static fn (Zone $zone) => $zone->isCityGrouper() ? null : $zone->getId(), $zones));

            if ($parents) {
                if (!\in_array($zoneParentAlias, $zoneQueryBuilder->getAllAliases(), true)) {
                    $zoneQueryBuilder->innerJoin($zoneRelationAlias.'.parents', $zoneParentAlias);
                }

                $orX->add(\sprintf('%s IN (:zone_parent_ids)', $zoneParentAlias));
                $zoneQueryBuilder->setParameter('zone_parent_ids', $parents);
            }
        }

        return $zoneQueryBuilder->where($orX);
    }
}
