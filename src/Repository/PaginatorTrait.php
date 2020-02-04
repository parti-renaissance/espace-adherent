<?php

namespace AppBundle\Repository;

use ApiPlatform\Core\Bridge\Doctrine\Orm\Paginator as ApiPaginator;
use ApiPlatform\Core\DataProvider\PaginatorInterface;
use Doctrine\ORM\QueryBuilder;
use Doctrine\ORM\Tools\Pagination\Paginator as DoctrinePaginator;

trait PaginatorTrait
{
    protected function configurePaginator(
        QueryBuilder $queryBuilder,
        int $page,
        int $limit = 30,
        callable $queryModifier = null
    ): PaginatorInterface {
        if ($page < 1) {
            $page = 1;
        }

        $query = $queryBuilder
            ->setMaxResults($limit)
            ->setFirstResult(($page - 1) * $limit)
            ->getQuery()
        ;

        if ($queryModifier) {
            $queryModifier($query);
        }

        return new ApiPaginator(new DoctrinePaginator($query));
    }
}
