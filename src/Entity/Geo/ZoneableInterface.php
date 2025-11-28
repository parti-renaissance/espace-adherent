<?php

declare(strict_types=1);

namespace App\Entity\Geo;

interface ZoneableInterface extends GeoInterface
{
    /**
     * @return self[]
     */
    public function getParents(): array;

    public function getZoneType(): string;
}
