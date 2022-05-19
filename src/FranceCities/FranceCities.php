<?php

namespace App\FranceCities;

use App\Entity\Geo\Zone;

class FranceCities
{
    private static $cities = [];
    private static $citiesByInseeCode = [];
    private CitiesStorageInterface $citiesStorage;

    public static $countries = [
        '98000' => 'MC', // Monaco
        '971' => 'GP', // Guadeloupe
        '972' => 'MQ', // Martinique
        '973' => 'GF', // Guyane
        '974' => 'RE', // Réunion
        '975' => 'PM', // Saint-Pierre-et-Miquelon
        '976' => 'YT', // Mayotte
        '986' => 'WF', // Wallis-et-Futuna
        '987' => 'PF', // Polynésie
        '988' => 'NC', // Nouvelle Calédonie
    ];

    public function __construct(CitiesStorageInterface $citiesStorage)
    {
        $this->citiesStorage = $citiesStorage;
    }

    public function findCitiesByPostalCode(string $postalCode): array
    {
        return $this->findCitiesForPostalCode($this->getCitiesList(), $postalCode);
    }

    public function getCitiesByInseeCode(): array
    {
        if (empty(self::$citiesByInseeCode)) {
            foreach ($this->getCitiesList() as $inseeCode => $city) {
                self::$citiesByInseeCode[str_pad($inseeCode, 5, '0', \STR_PAD_LEFT)] = $city['name'];
            }
        }

        return self::$citiesByInseeCode;
    }

    public function getCityInseeCode(string $postalCode, string $name): ?string
    {
        $normalizedName = $this->canonicalizeCityName($name);

        foreach ($this->getCitiesList() as $inseeCode => $city) {
            if (
                \in_array($postalCode, $city['postal_code'], true)
                && str_starts_with($this->canonicalizeCityName($city['name']), $normalizedName)
            ) {
                return $inseeCode;
            }
        }

        return null;
    }

    public function getCountryISOCode(string $postalCode): string
    {
        foreach (self::$countries as $prefix => $country) {
            if (0 === strpos($postalCode, (string) $prefix)) {
                return $country;
            }
        }

        return 'FR';
    }

    public function searchCities(string $search, int $maxResults = 20, array $ignore = [], array $filters = []): array
    {
        $search = $this->canonicalizeCityName($search);
        $citiesList = $this->getCitiesList();

        $results = [];
        foreach ($citiesList as $inseeCode => $city) {
            $inseeCode = str_pad($inseeCode, 5, '0', \STR_PAD_LEFT);

            $matchFilters = false;
            foreach ($filters as $filter) {
                if (0 === strpos($inseeCode, $filter)) {
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
                if (0 !== strpos($this->canonicalizeCityName($city['name']), $search)) {
                    continue;
                }
            } else {
                if (0 !== strpos(implode(', ', $city['postal_code']), $search)) {
                    continue;
                }
            }

            $results[$inseeCode] = $city;

            if (is_numeric($search) && \count($results) >= $maxResults) {
                break;
            }
        }

        if (!is_numeric($search)) {
            usort($results, function (array $city1, array $city2) {
                return $city1['name'] <=> $city2['name'];
            });
        }

        return $results;
    }

    /**
     * @param Zone[]|array $zones
     */
    public function searchCitiesForZones(array $zones, string $search, int $maxResults = 10): array
    {
        $filters = [];
        foreach ($zones as $zone) {
            $filters[] = $zone->getCode();
        }

        return $this->searchCities($search, $maxResults, [], $filters);
    }

    public function searchCitiesByInseeCodes(array $inseeCodes): array
    {
        $results = array_intersect_key(
            $this->getCitiesByInseeCode(),
            array_flip($inseeCodes)
        );
        asort($results);

        return $results;
    }

    public function getCityByInseeCode(string $inseeCode): ?array
    {
        return $this->getCitiesList()[$inseeCode] ?? null;
    }

    public function getCityNameByInseeCode(string $inseeCode): ?string
    {
        return $this->getCityByInseeCode($inseeCode)['name'] ?? null;
    }

    private function getCitiesList(): array
    {
        if (empty(self::$cities)) {
            self::$cities = $this->citiesStorage->getCitiesList();
        }

        return self::$cities;
    }

    private function findCitiesForPostalCode(array $citiesList, string $postalCode): array
    {
        $citiesFoundedList = [];

        foreach ($citiesList as $inseeCode => $city) {
            if (\in_array($postalCode, $city['postal_code'], true)) {
                $citiesFoundedList[$inseeCode] = $city['name'];
            }
        }

        return $citiesFoundedList;
    }

    private function canonicalizeCityName(string $cityName): string
    {
        $cityName = iconv('UTF-8', 'ASCII//TRANSLIT', $cityName);

        return mb_strtolower(str_replace('-', ' ', trim($cityName)));
    }
}
