<?php

declare(strict_types=1);

namespace App\FranceCities;

interface CitiesStorageInterface
{
    public function getCitiesList(): array;
}
