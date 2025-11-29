<?php

declare(strict_types=1);

namespace App\Api\Filter;

use App\Entity\Adherent;
use App\Entity\Jecoute\News;
use App\Scope\Generator\ScopeGeneratorInterface;
use App\Scope\ScopeEnum;
use Doctrine\ORM\QueryBuilder;

final class JecouteNewsScopeFilter extends AbstractScopeFilter
{
    protected function needApplyFilter(string $property, string $resourceClass): bool
    {
        return is_a($resourceClass, News::class, true);
    }

    protected function applyFilter(
        QueryBuilder $queryBuilder,
        Adherent $currentUser,
        ScopeGeneratorInterface $scopeGenerator,
        string $resourceClass,
        array $context,
    ): void {
        $alias = $queryBuilder->getRootAliases()[0];

        switch ($scopeGenerator->getCode()) {
            case ScopeEnum::NATIONAL:
                $queryBuilder
                    ->andWhere(\sprintf('%s.authorInstance = :national', $alias))
                    ->setParameter('national', ScopeEnum::SCOPE_INSTANCES[ScopeEnum::NATIONAL])
                ;

                break;
        }
    }

    protected function getAllowedOperationNames(string $resourceClass): array
    {
        return ['_api_/v3/jecoute/news/{uuid}_get', '_api_/v3/jecoute/news_get_collection'];
    }
}
