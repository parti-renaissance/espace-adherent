<?php

namespace App\Doctrine\Utils;

use Doctrine\ORM\Query\Expr\Join;
use Doctrine\ORM\QueryBuilder;

abstract class QueryChecker
{
    public static function hasJoin(QueryBuilder $qb, string $alias, string $join): bool
    {
        $joins = $qb->getDQLPart('join');

        foreach ($joins[$alias] ?? [] as $existingJoin) {
            if ($existingJoin instanceof Join && $existingJoin->getJoin() === $alias.'.'.$join) {
                return true;
            }
        }

        return false;
    }
}
