<?php

namespace App\Entity\Geo;

interface GeoInterface
{
    public function getName(): ?string;

    public function getCode(): ?string;
}
