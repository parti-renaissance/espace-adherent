<?php

namespace App\Algolia\Query\Expr;

use App\Algolia\Query\QueryBuilder;

abstract class AbstractExpr implements ExprInterface
{
    protected $arguments;

    public function __construct(array $arguments)
    {
        $this->arguments = $arguments;
    }

    public function toString(array $params): string
    {
        return implode(sprintf(' %s ', $this->getSeparator()), $this->getParts($params));
    }

    protected function getParts(array $params): array
    {
        $parts = [];
        foreach ($this->arguments as $argument) {
            if ($argument instanceof QueryBuilder) {
                $parts[] = $argument->getQuery($params);
                continue;
            }

            if ($argument instanceof ExprInterface) {
                $parts[] = $argument->toString($params);
                continue;
            }

            $parts[] = (string) $argument;
        }

        return $parts;
    }

    abstract protected function getSeparator(): string;
}
