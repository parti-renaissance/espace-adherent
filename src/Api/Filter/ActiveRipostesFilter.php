<?php

namespace App\Api\Filter;

use ApiPlatform\Core\Bridge\Doctrine\Orm\Filter\AbstractContextAwareFilter;
use ApiPlatform\Core\Bridge\Doctrine\Orm\Util\QueryNameGeneratorInterface;
use App\Entity\Jecoute\Riposte;
use Doctrine\ORM\QueryBuilder;

final class ActiveRipostesFilter extends AbstractContextAwareFilter
{
    private const PROPERTY_NAME = 'active';

    protected function filterProperty(
        string $property,
        $value,
        QueryBuilder $queryBuilder,
        QueryNameGeneratorInterface $queryNameGenerator,
        string $resourceClass,
        string $operationName = null
    ) {
        if (Riposte::class !== $resourceClass || self::PROPERTY_NAME !== $property) {
            return;
        }

        $alias = $queryBuilder->getRootAliases()[0];
        $queryBuilder
            ->andWhere("$alias.enabled = :true AND $alias.createdAt > :last_24")
            ->setParameter('true', true)
            ->setParameter('last_24', new \DateTime('-24 hours'))
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
