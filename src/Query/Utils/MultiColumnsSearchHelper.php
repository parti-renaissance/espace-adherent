<?php

namespace App\Query\Utils;

use App\Query\Mysql\Sluggify;
use Doctrine\ORM\QueryBuilder;

class MultiColumnsSearchHelper
{
    public static function updateQueryBuilderForMultiColumnsSearch(
        QueryBuilder $queryBuilder,
        string $searchTerm,
        array $mainColumns,
        array $additionalColumns = [],
        array $columnStrictSearch = []
    ): void {
        $conditions = $queryBuilder->expr()->orX();

        $searchCharactersPattern = '/'.Sluggify::REGEXP_PATTERN.'/';

        preg_match('/(?<first>[^\s]*)[\s]*(?<last>.*)/', $searchTerm, $tokens);

        if (\array_key_exists('first', $tokens) && \array_key_exists('last', $tokens)) {
            foreach ($mainColumns as $coupleColumn) {
                $conditions->add("SLUGGIFY($coupleColumn[0]) LIKE :search_first_token AND SLUGGIFY($coupleColumn[1]) LIKE :search_last_token");
            }

            $queryBuilder
                ->setParameter('search_first_token', '%'.preg_replace($searchCharactersPattern, '', $tokens['first']).'%')
                ->setParameter('search_last_token', '%'.preg_replace($searchCharactersPattern, '', $tokens['last']).'%')
            ;
        } else {
            foreach ($mainColumns as $coupleColumn) {
                $conditions->add("SLUGGIFY($coupleColumn[0]) LIKE :slug_search");
            }

            $queryBuilder->setParameter('slug_search', '%'.preg_replace($searchCharactersPattern, '', $searchTerm).'%');
        }

        if ($additionalColumns) {
            foreach ($additionalColumns as $column) {
                $conditions->add("SLUGGIFY($column) LIKE :slug_search");
            }

            $queryBuilder->setParameter('slug_search', '%'.preg_replace($searchCharactersPattern, '', $searchTerm).'%');
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
