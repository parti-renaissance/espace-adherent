<?php

namespace App\Algolia\Query\Expr;

class AndWhere extends AbstractExpr
{
    protected function getSeparator(): string
    {
        return 'AND';
    }
}
