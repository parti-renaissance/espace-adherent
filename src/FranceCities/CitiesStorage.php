<?php

declare(strict_types=1);

namespace App\FranceCities;

use App\Repository\Geo\ZoneRepository;

class CitiesStorage implements CitiesStorageInterface
{
    private ZoneRepository $zoneRepository;

    public function __construct(ZoneRepository $zoneRepository)
    {
        $this->zoneRepository = $zoneRepository;
    }

    public function getCitiesList(): array
    {
        return $this->zoneRepository->getFrenchCities();
    }
}
