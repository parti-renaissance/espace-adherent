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

        preg_match('/(?<first>[^\s]*)[\s]*(?<last>.*)/', $searchTerm, $tokens);

        if (\array_key_exists('first', $tokens) && \array_key_exists('last', $tokens)) {
            $useSecondColumn = !empty($tokens['last']);

            foreach ($mainColumns as $coupleColumn) {
                $conditions->add(\sprintf('%s LIKE :search_first_token %s', $coupleColumn[0], $useSecondColumn ? "AND $coupleColumn[1] LIKE :search_last_token" : ''));
            }

            $queryBuilder->setParameter('search_first_token', '%'.$tokens['first'].'%');
            if ($useSecondColumn) {
                $queryBuilder->setParameter('search_last_token', '%'.$tokens['last'].'%');
            }
        } else {
            foreach ($mainColumns as $coupleColumn) {
                $conditions->add("$coupleColumn[0] LIKE :slug_search");
            }

            $queryBuilder->setParameter('slug_search', '%'.$searchTerm.'%');
        }

        if ($additionalColumns) {
            foreach ($additionalColumns as $column) {
                $conditions->add("$column LIKE :slug_search");
            }

            $queryBuilder->setParameter('slug_search', '%'.$searchTerm.'%');
        }

        if ($columnStrictSearch) {
            foreach ($columnStrictSearch as $column) {
                $conditions->add("$column = :strict_search");
            }

            $queryBuilder->setParameter('strict_search', $searchTerm);
        }

        $queryBuilder->andWhere($conditions);
    }
}
