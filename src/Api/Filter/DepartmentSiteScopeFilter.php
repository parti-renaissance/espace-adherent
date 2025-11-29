<?php

declare(strict_types=1);

namespace App\Api\Filter;

use App\Entity\Adherent;
use App\Entity\DepartmentSite\DepartmentSite;
use App\Scope\Generator\ScopeGeneratorInterface;
use Doctrine\ORM\QueryBuilder;

class DepartmentSiteScopeFilter extends AbstractScopeFilter
{
    protected function needApplyFilter(string $property, string $resourceClass): bool
    {
        return is_a($resourceClass, DepartmentSite::class, true);
    }

    protected function applyFilter(
        QueryBuilder $queryBuilder,
        Adherent $currentUser,
        ScopeGeneratorInterface $scopeGenerator,
        string $resourceClass,
        array $context,
    ): void {
        $alias = $queryBuilder->getRootAliases()[0];
        $scope = $scopeGenerator->generate($currentUser);

        $queryBuilder
            ->andWhere("$alias.zone IN (:zones)")
            ->setParameter('zones', $scope->getZones())
        ;
    }
}
