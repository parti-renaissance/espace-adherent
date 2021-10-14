<?php

namespace App\Api\Filter;

use ApiPlatform\Core\Bridge\Doctrine\Orm\Filter\AbstractContextAwareFilter;
use ApiPlatform\Core\Bridge\Doctrine\Orm\Util\QueryNameGeneratorInterface;
use App\Entity\Team\Team;
use App\Scope\ScopeEnum;
use App\Team\TypeEnum;
use Doctrine\ORM\QueryBuilder;

class TeamsTypeFilter extends AbstractContextAwareFilter
{
    private const PROPERTY_NAME = 'scope';
    private const ACTIONS = ['get'];
    private const TYPES_MAPPING = [
        ScopeEnum::PHONING_NATIONAL_MANAGER => TypeEnum::PHONING,
    ];

    protected function filterProperty(
        string $property,
        $value,
        QueryBuilder $queryBuilder,
        QueryNameGeneratorInterface $queryNameGenerator,
        string $resourceClass,
        string $operationName = null
    ) {
        if (
            !is_a($resourceClass, Team::class, true)
            || self::PROPERTY_NAME !== $property
            || !\in_array($operationName, self::ACTIONS, true)
        ) {
            return;
        }

        $alias = $queryBuilder->getRootAliases()[0];

        $queryBuilder
            ->andWhere("$alias.type = :type")
            ->setParameter('type', self::TYPES_MAPPING[$value])
        ;
    }

    public function getDescription(string $resourceClass): array
    {
        return [
            self::PROPERTY_NAME => [
                'property' => null,
                'type' => 'string',
                'required' => false,
            ],
        ];
    }
}
