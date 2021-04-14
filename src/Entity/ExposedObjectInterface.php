<?php

namespace App\Entity;

interface ExposedObjectInterface
{
    public function getNormalizationGroups(): array;

    public function getUrlParamName(): ?string;

    public function getExposedRouteName(): string;

    public function getExposedRouteParams(): array;
}
