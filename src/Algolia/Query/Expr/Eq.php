<?php

namespace App\Algolia\Query\Expr;

class Eq extends AbstractExpr
{
    public function toString(array $params): string
    {
        foreach ($this->getParts($params) as $part) {
            $parts = explode(' = ', $part);

            $key = ltrim($parts[1], ':');

            return sprintf(
                '%s:%s',
                ltrim($parts[0], '.'),
                $params[$key]
            );
        }

        return '';
    }

    protected function getSeparator(): string
    {
        return '';
    }
}
