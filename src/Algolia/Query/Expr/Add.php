<?php

namespace App\Algolia\Query\Expr;

class Add extends AbstractExpr
{
    protected function getSeparator(): string
    {
        return 'AND';
    }
}
