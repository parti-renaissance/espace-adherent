<?php

namespace App\Algolia\Query;

use App\Algolia\Query\Expr\AbstractExpr;
use App\Algolia\Query\Expr\Eq;
use App\Algolia\Query\Expr\ExprInterface;
use App\Algolia\Query\Expr\Like;

class QueryBuilder
{
    /** @var ExprInterface[] */
    private $parts = [];
    private $parameters = [];

    public function expr(): self
    {
        return new self();
    }

    public function setParameter(string $name, $value): self
    {
        $this->parameters[$name] = $value;

        return $this;
    }

    public function __call(string $method, array $arguments): self
    {
        $className = str_replace('AbstractExpr', ucfirst($method), AbstractExpr::class);
        /** @var ExprInterface $expr */
        $expr = new $className(array_map(function ($value) {
            if (\is_string($value)) {
                if (false !== strpos($value, ' LIKE ')) {
                    return new Like([$value]);
                }

                if (false !== strpos($value, ' = ')) {
                    return new Eq([$value]);
                }
            }

            return $value;
        }, $arguments));

        $this->parts[] = $expr;

        return $this;
    }

    public function getQuery(array $parameters = []): string
    {
        if ([] === $parameters) {
            $parameters = $this->parameters;
        }

        $parts = [];

        foreach ($this->parts as $expr) {
            if ($expression = $expr->toString($parameters)) {
                $parts[] = $expression;
            }
        }

        return implode(' AND ', $parts);
    }
}
