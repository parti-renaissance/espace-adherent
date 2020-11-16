<?php

namespace App\Algolia\Query\Expr;

class In extends AbstractExpr
{
    public function toString(array $params): string
    {
        $name = $this->arguments[0];
        $params = $params[ltrim($this->arguments[1], ':')];

        $tmp = [];
        foreach ($params as $value) {
            if (\is_object($value)) {
                $value = $value->getId();
            }

            $tmp[] = "${name}:${value}";
        }

        return sprintf('(%s)', implode(' OR ', $tmp));
    }

    protected function getSeparator(): string
    {
        return '';
    }
}
