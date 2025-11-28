<?php

declare(strict_types=1);

namespace App\Api\Doctrine;

use ApiPlatform\Doctrine\Orm\Extension\QueryCollectionExtensionInterface;
use ApiPlatform\Doctrine\Orm\Util\QueryNameGeneratorInterface;
use ApiPlatform\Metadata\Operation;
use App\Entity\Geo\Zone;
use App\Entity\ProcurationV2\AbstractProcuration;
use App\Scope\ScopeGeneratorResolver;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\QueryBuilder;
use Symfony\Bundle\SecurityBundle\Security;

class ProcurationExtension implements QueryCollectionExtensionInterface
{
    public function __construct(
        private readonly ScopeGeneratorResolver $scopeResolver,
        private readonly Security $security,
        private readonly EntityManagerInterface $entityManager,
    ) {
    }

    public function applyToCollection(
        QueryBuilder $queryBuilder,
        QueryNameGeneratorInterface $queryNameGenerator,
        string $resourceClass,
        ?Operation $operation = null,
        array $context = [],
    ): void {
        if (!is_a($resourceClass, AbstractProcuration::class, true)) {
            return;
        }

        if (!$scopeGenerator = $this->scopeResolver->resolve()) {
            return;
        }

        $scope = $scopeGenerator->generate($this->security->getUser());

        $zones = $scope->getZones();

        $zoneQueryBuilder = $this->entityManager
            ->createQueryBuilder()
            ->select($select = \sprintf('%s.id', $entityClassAlias = 'procu_filter_subquery'))
            ->from($resourceClass, $entityClassAlias)
            ->leftJoin($entityClassAlias.'.voteZone', 'vote_zone')
            ->leftJoin($entityClassAlias.'.votePlace', 'vote_place')
            ->groupBy($select)
        ;

        $orX = $queryBuilder->expr()->orX()
            ->add('vote_zone.id IN (:procuration_zone_ids)')
            ->add('vote_place.id IN (:procuration_zone_ids)')
        ;

        $queryBuilder->setParameter('procuration_zone_ids', $zoneIds = array_map(static function (Zone $zone): int {
            return $zone->getId();
        }, $zones));

        $zoneQueryBuilder
            ->leftJoin('vote_place.parents', 'vote_place_parent')
            ->leftJoin('vote_zone.parents', 'vote_zone_parent')
        ;

        $orX
            ->add('vote_place_parent IN (:procuration_zone_parent_ids)')
            ->add($entityClassAlias.'.votePlace IS NULL AND vote_zone_parent IN (:procuration_zone_parent_ids)')
        ;
        $queryBuilder->setParameter('procuration_zone_parent_ids', $zoneIds);

        $zoneQueryBuilder->where($orX);

        $queryBuilder->andWhere(\sprintf('%s.id IN (%s)', $queryBuilder->getRootAliases()[0], $zoneQueryBuilder->getDQL()));
    }
}
