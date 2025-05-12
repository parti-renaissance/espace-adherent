<?php

namespace App\Query\Utils;

use Doctrine\ORM\QueryBuilder;

class MultiColumnsSearchHelper
{
    public static function updateQueryBuilderForMultiColumnsSearch(
        QueryBuilder $queryBuilder,
        string $searchTerm,
        array $mainColumns,
        array $additionalColumns = [],
        array $columnStrictSearch = [],
    ): void {
        $conditions = $queryBuilder->expr()->orX();
        $uniqueKey = uniqid();

        preg_match('/(?<first>[^\s]*)[\s]*(?<last>.*)/', $searchTerm, $tokens);

        if (\array_key_exists('first', $tokens) && \array_key_exists('last', $tokens)) {
            $useSecondColumn = !empty($tokens['last']);

            foreach ($mainColumns as $coupleColumn) {
                $conditions->add(\sprintf('%s LIKE :search_first_token_%s %s', $coupleColumn[0], $uniqueKey, $useSecondColumn ? "AND $coupleColumn[1] LIKE :search_last_token_$uniqueKey" : ''));
            }

            $queryBuilder->setParameter('search_first_token_'.$uniqueKey, '%'.$tokens['first'].'%');
            if ($useSecondColumn) {
                $queryBuilder->setParameter('search_last_token_'.$uniqueKey, '%'.$tokens['last'].'%');
            }
        } else {
            foreach ($mainColumns as $coupleColumn) {
                $conditions->add("$coupleColumn[0] LIKE :slug_search_$uniqueKey");
            }

            $queryBuilder->setParameter('slug_search_'.$uniqueKey, '%'.$searchTerm.'%');
        }

        if ($additionalColumns) {
            foreach ($additionalColumns as $column) {
                $conditions->add("$column LIKE :slug_search_$uniqueKey");
            }

            $queryBuilder->setParameter('slug_search_'.$uniqueKey, '%'.$searchTerm.'%');
        }

        if ($columnStrictSearch) {
            foreach ($columnStrictSearch as $column) {
                $conditions->add("$column = :strict_search_$uniqueKey");
            }

            $queryBuilder->setParameter('strict_search_'.$uniqueKey, $searchTerm);
        }

        $queryBuilder->andWhere($conditions);
    }
}
