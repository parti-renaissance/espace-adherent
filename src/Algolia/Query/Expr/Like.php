<?php

namespace App\Algolia\Query\Expr;

class Like extends AbstractExpr
{
    public function toString(array $params): string
    {
        foreach ($this->getParts($params) as $part) {
            $parts = explode(' LIKE ', $part);

            $key = ltrim($parts[1], ':');

            return sprintf(
                '%s:%s',
                ltrim($parts[0], '.'),
                trim($params[$key], '%')
            );
        }

        return '';
    }

    protected function getSeparator(): string
    {
        return '';
    }
}
