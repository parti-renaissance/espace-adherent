<?php

namespace App\Api\Filter;

use App\Entity\Adherent;
use App\Entity\Pap\VotePlace;
use App\Repository\Pap\VotePlaceRepository;
use App\Scope\Generator\ScopeGeneratorInterface;
use Doctrine\ORM\QueryBuilder;

final class PapVotePlaceScopeFilter extends AbstractScopeFilter
{
    private ?VotePlaceRepository $repository = null;

    /** @required */
    public function setVotePlaceRepository(VotePlaceRepository $repository)
    {
        $this->repository = $repository;
    }

    protected function needApplyFilter(string $property, string $resourceClass, string $operationName = null): bool
    {
        return is_a($resourceClass, VotePlace::class, true);
    }

    protected function applyFilter(
        QueryBuilder $queryBuilder,
        Adherent $currentUser,
        ScopeGeneratorInterface $scopeGenerator
    ): void {
        $alias = $queryBuilder->getRootAliases()[0];
        $scope = $scopeGenerator->generate($currentUser);

        if ($scope->isNational()) {
            // for national scope return 0 vote place instead of all 60K
            $queryBuilder->setMaxResults(0);
        } else {
            $this->repository->withZones($queryBuilder, $scope->getZones(), $alias);
        }
    }
}
