<?php

namespace App\Api\Filter;

use ApiPlatform\Doctrine\Orm\Filter\AbstractFilter;
use ApiPlatform\Doctrine\Orm\Util\QueryNameGeneratorInterface;
use ApiPlatform\Metadata\Operation;
use App\Doctrine\Utils\QueryChecker;
use App\Query\Utils\MultiColumnsSearchHelper;
use Doctrine\ORM\QueryBuilder;
use Symfony\Component\PropertyInfo\Type;

final class ReferralFilter extends AbstractFilter
{
    protected function filterProperty(
        string $property,
        $value,
        QueryBuilder $queryBuilder,
        QueryNameGeneratorInterface $queryNameGenerator,
        string $resourceClass,
        ?Operation $operation = null,
        array $context = [],
    ): void {
        if (empty($value) || !\in_array($property, ['referred', 'referrer'], true)) {
            return;
        }

        $alias = $filterRelationAlias = $queryBuilder->getRootAliases()[0];

        if ('referrer' === $property) {
            if (!QueryChecker::hasJoin($queryBuilder, $alias, $filterRelationAlias = 'referrer_filter')) {
                $queryBuilder->innerJoin("$alias.referrer", $filterRelationAlias);
            }
        } elseif ('referred' === $property) {
            if (!QueryChecker::hasJoin($queryBuilder, $alias, $filterRelationAlias = 'referred_filter')) {
                $queryBuilder->leftJoin("$alias.referred", $filterRelationAlias);
            }
        }

        MultiColumnsSearchHelper::updateQueryBuilderForMultiColumnsSearch(
            $queryBuilder,
            $value,
            array_merge('referred' === $property ? [
                ["$alias.firstName", "$alias.lastName"],
                ["$alias.lastName", "$alias.firstName"],
                ["$alias.emailAddress", "$alias.emailAddress"],
            ] : [], [
                ["$filterRelationAlias.firstName", "$filterRelationAlias.lastName"],
                ["$filterRelationAlias.lastName", "$filterRelationAlias.firstName"],
                ["$filterRelationAlias.emailAddress", "$filterRelationAlias.emailAddress"],
            ]),
        );
    }

    public function getDescription(string $resourceClass): array
    {
        return [
            'referred' => [
                'property' => null,
                'type' => Type::BUILTIN_TYPE_STRING,
                'required' => false,
                'swagger' => [
                    'description' => 'Search referred person by firstName, lastName or emailAddress',
                ],
            ],
            'referrer' => [
                'property' => null,
                'type' => Type::BUILTIN_TYPE_STRING,
                'required' => false,
                'swagger' => [
                    'description' => 'Search referrer person by firstName, lastName or emailAddress',
                ],
            ],
        ];
    }
}
