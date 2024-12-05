<?php

namespace App\Api\Filter;

use ApiPlatform\Doctrine\Orm\Filter\AbstractFilter;
use ApiPlatform\Doctrine\Orm\Util\QueryNameGeneratorInterface;
use ApiPlatform\Metadata\Operation;
use App\Entity\Jecoute\Survey;
use App\Jecoute\SurveyTypeEnum;
use Doctrine\ORM\QueryBuilder;

final class SurveyTypeFilter extends AbstractFilter
{
    private const PROPERTY_NAME = 'type';

    protected function filterProperty(
        string $property,
        $value,
        QueryBuilder $queryBuilder,
        QueryNameGeneratorInterface $queryNameGenerator,
        string $resourceClass,
        ?Operation $operation = null,
        array $context = [],
    ): void {
        if (
            Survey::class !== $resourceClass
            || self::PROPERTY_NAME !== $property
            || !\in_array($value, SurveyTypeEnum::toArray())
        ) {
            return;
        }

        $alias = $queryBuilder->getRootAliases()[0];

        $queryBuilder
            ->andWhere("$alias INSTANCE OF :instance")
            ->setParameter('instance', $value)
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
