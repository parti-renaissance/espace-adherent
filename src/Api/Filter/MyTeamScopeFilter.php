<?php

declare(strict_types=1);

namespace App\Api\Filter;

use App\Entity\Adherent;
use App\Entity\MyTeam\MyTeam;
use App\Scope\Generator\ScopeGeneratorInterface;
use Doctrine\ORM\QueryBuilder;

final class MyTeamScopeFilter extends AbstractScopeFilter
{
    protected function needApplyFilter(string $property, string $resourceClass): bool
    {
        return is_a($resourceClass, MyTeam::class, true);
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
            ->andWhere("$alias.owner = :user AND $alias.scope = :scope")
            ->setParameters([
                'user' => $scope->getDelegator() ?? $currentUser,
                'scope' => $scope->getMainCode(),
            ])
        ;
    }
}
