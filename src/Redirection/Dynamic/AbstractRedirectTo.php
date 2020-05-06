<?php

namespace App\Redirection\Dynamic;

abstract class AbstractRedirectTo
{
    public function hasPattern(string $pattern, string $requestUri): bool
    {
        return 0 === strpos($requestUri, $pattern);
    }
}
