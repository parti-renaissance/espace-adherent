<?php

namespace App\FranceCities;

interface CitiesStorageInterface
{
    public function getCitiesList(): array;
}
