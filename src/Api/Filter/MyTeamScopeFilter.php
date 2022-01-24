<?php

namespace App\Api\Filter;

use App\Entity\Adherent;
use App\Entity\MyTeam\MyTeam;
use App\Scope\Generator\ScopeGeneratorInterface;
use Doctrine\ORM\QueryBuilder;

final class MyTeamScopeFilter extends AbstractScopeFilter
{
    protected function needApplyFilter(string $property, string $resourceClass, string $operationName = null): bool
    {
        return is_a($resourceClass, MyTeam::class, true);
    }

    protected function applyFilter(
        QueryBuilder $queryBuilder,
        Adherent $currentUser,
        ScopeGeneratorInterface $scopeGenerator
    ): void {
        $alias = $queryBuilder->getRootAliases()[0];
        $scope = $scopeGenerator->generate($currentUser);
        $queryBuilder
            ->andWhere("$alias.owner = :user AND $alias.scope = :scope")
            ->setParameters([
                'user' => $currentUser,
                'scope' => $scope->getCode(),
            ])
        ;
    }
}
