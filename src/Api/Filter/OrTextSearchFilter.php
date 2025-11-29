<?php

declare(strict_types=1);

namespace App\Api\Filter;

use ApiPlatform\Doctrine\Orm\Filter\AbstractFilter;
use ApiPlatform\Doctrine\Orm\Util\QueryNameGeneratorInterface;
use ApiPlatform\Metadata\Operation;
use App\Query\Utils\MultiColumnsSearchHelper;
use Doctrine\ORM\QueryBuilder;

final class OrTextSearchFilter extends AbstractFilter
{
    public const PROPERTY_SUFFIX = '_contains';

    protected function filterProperty(
        string $property,
        $value,
        QueryBuilder $queryBuilder,
        QueryNameGeneratorInterface $queryNameGenerator,
        string $resourceClass,
        ?Operation $operation = null,
        array $context = [],
    ): void {
        if ('search' !== $property || empty($value)) {
            return;
        }

        $alias = $queryBuilder->getRootAliases()[0];

        $columnsConfig = [];
        foreach ($this->properties as $firstColumn => $secondColumn) {
            if (str_contains($firstColumn, '.')) {
                $joinTableFromFirstColumn = explode('.', $firstColumn, 2);
                $queryBuilder->leftJoin($alias.'.'.$joinTableFromFirstColumn[0], $aliasFirstColumnJoinTable = $alias.'_'.$joinTableFromFirstColumn[0]);
                $columnsConfig[] = ["$aliasFirstColumnJoinTable.$joinTableFromFirstColumn[1]", "$aliasFirstColumnJoinTable.$joinTableFromFirstColumn[1]"];
                continue;
            }

            $columnsConfig[] = ["$alias.$firstColumn", "$alias.$secondColumn"];
        }

        MultiColumnsSearchHelper::updateQueryBuilderForMultiColumnsSearch(
            $queryBuilder,
            $value,
            $columnsConfig
        );
    }

    public function getDescription(string $resourceClass): array
    {
        if (!$this->properties) {
            return [];
        }

        $description = [];
        foreach ($this->properties as $property) {
            $description[$property.self::PROPERTY_SUFFIX] = [
                'property' => $property.self::PROPERTY_SUFFIX,
                'type' => 'string',
                'required' => false,
                'swagger' => [
                    'description' => 'Filter by containing one of the words of searching string.',
                    'name' => $property.self::PROPERTY_SUFFIX,
                    'type' => 'string',
                ],
            ];
        }

        return $description;
    }
}
