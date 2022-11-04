<?php

namespace App\Redirection\Dynamic;

abstract class AbstractRedirectTo
{
    public function hasPattern(string $pattern, string $requestUri): bool
    {
        return str_starts_with($requestUri, $pattern);
    }
}
