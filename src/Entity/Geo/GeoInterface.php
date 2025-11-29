<?php

declare(strict_types=1);

namespace App\Entity\Geo;

use App\Entity\GeoData;

interface GeoInterface
{
    public const CITY_PARIS_CODE = '75056';
    public const CITY_LYON_CODE = '69123';
    public const CITY_MARSEILLE_CODE = '13055';

    public const DEPARTMENT_PARIS_CODE = '75';

    public function getId(): ?int;

    public function getCode(): string;

    public function getName(): string;

    public function setName(string $name): void;

    public function isActive(): bool;

    public function activate(bool $active = true): void;

    public function getGeoData(): ?GeoData;

    public function setGeoData(?GeoData $geoData): void;
}
