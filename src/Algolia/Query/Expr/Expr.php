<?php

namespace App\Algolia\Query\Expr;

class Expr extends AbstractExpr
{
    protected function getSeparator(): string
    {
        return 'AND';
    }
}
