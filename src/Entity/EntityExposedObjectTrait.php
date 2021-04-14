<?php

namespace App\Entity;

trait EntityExposedObjectTrait
{
    public function getUrlParamName(): ?string
    {
        return null;
    }

    public function getExposedRouteParams(): array
    {
        return [];
    }
}
