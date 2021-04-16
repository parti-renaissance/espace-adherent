<?php

namespace App\Entity;

interface ExposedObjectInterface
{
    public function getId(): ?int;

    public function getNormalizationGroups(): array;

    public function getExposedRouteName(): string;

    public function getExposedRouteParams(): array;
}
