<?php

namespace App\Algolia\Query\Expr;

class OrX extends AbstractExpr
{
    protected function getSeparator(): string
    {
        return 'OR';
    }
}
