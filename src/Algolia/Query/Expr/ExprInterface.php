<?php

namespace App\Algolia\Query\Expr;

interface ExprInterface
{
    public function toString(array $params): string;
}
