<?php

namespace App\Entity\Geo;

use App\Entity\GeoData;

interface GeoInterface
{
    public function getId(): ?int;

    public function getCode(): string;

    public function getName(): string;

    public function setName(string $name): void;

    public function isActive(): bool;

    public function activate(bool $active = true): void;

    public function getGeoData(): ?GeoData;

    public function setGeoData(?GeoData $geoData): void;
}
