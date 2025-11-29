<?php

declare(strict_types=1);

namespace App\FranceCities;

use App\Entity\Geo\Zone;

class FranceCities
{
    private static array $cities = [];
    private CitiesStorageInterface $citiesStorage;

    public function __construct(CitiesStorageInterface $citiesStorage)
    {
        $this->citiesStorage = $citiesStorage;
    }

    /** @return CityValueObject[] */
    public function findCitiesByPostalCode(string $postalCode): array
    {
        $citiesFoundedList = [];

        foreach ($this->getCitiesList() as $city) {
            if (\in_array($postalCode, $city['postal_code'], true)) {
                $citiesFoundedList[] = CityValueObject::createFromCityArray($city);
            }
        }

        return $citiesFoundedList;
    }

    public function getCityByPostalCodeAndName(string $postalCode, string $name): ?CityValueObject
    {
        $normalizedName = $this->canonicalizeCityName($name);

        foreach ($this->getCitiesList() as $city) {
            if (
                \in_array($postalCode, $city['postal_code'], true)
                && str_starts_with($this->canonicalizeCityName($city['name']), $normalizedName)
            ) {
                return CityValueObject::createFromCityArray($city);
            }
        }

        return null;
    }

    /** @return CityValueObject[] */
    public function searchCities(string $search, int $maxResults = 20, array $ignore = [], array $filters = []): array
    {
        $search = $this->canonicalizeCityName($search);
        $citiesList = $this->getCitiesList();

        $results = [];
        foreach ($citiesList as $inseeCode => $city) {
            $matchFilters = false;
            foreach ($filters as $filter) {
                if (str_starts_with((string) $inseeCode, $filter)) {
                    $matchFilters = true;
                    break;
                }
            }

            if (!empty($filters) && !$matchFilters) {
                continue;
            }

            if (\in_array($inseeCode, $ignore)) {
                continue;
            }

            if (!is_numeric($search)) {
                if (!str_starts_with($this->canonicalizeCityName($city['name']), $search)) {
                    continue;
                }
            } else {
                if (!str_starts_with(implode(', ', $city['postal_code']), $search)) {
                    continue;
                }
            }

            $results[] = CityValueObject::createFromCityArray($city);

            if (is_numeric($search) && \count($results) >= $maxResults) {
                break;
            }
        }

        if (!is_numeric($search)) {
            usort($results, function (CityValueObject $city1, CityValueObject $city2) {
                return $city1->getName() <=> $city2->getName();
            });
        }

        return $results;
    }

    /**
     * @param Zone[] $zones
     *
     * @return CityValueObject[]
     */
    public function searchCitiesForZones(array $zones, string $search, int $maxResults = 10): array
    {
        $filters = [];
        foreach ($zones as $zone) {
            $filters[] = $zone->getCode();
        }

        return $this->searchCities($search, $maxResults, [], $filters);
    }

    public function getCityByInseeCode(string $inseeCode): ?CityValueObject
    {
        return isset($this->getCitiesList()[$inseeCode]) ? CityValueObject::createFromCityArray($this->getCitiesList()[$inseeCode]) : null;
    }

    private function getCitiesList(): array
    {
        if (empty(self::$cities)) {
            self::$cities = $this->citiesStorage->getCitiesList();
        }

        return self::$cities;
    }

    private function canonicalizeCityName(string $cityName): string
    {
        $cityName = iconv('UTF-8', 'ASCII//TRANSLIT', $cityName);

        return mb_strtolower(str_replace('-', ' ', trim($cityName)));
    }
}
