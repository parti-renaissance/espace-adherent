<?php

namespace App\FranceCities;

use App\Entity\ReferentTag;
use App\Utils\AreaUtils;

class FranceCities
{
    private static $cities = [];
    private static $citiesByInseeCode = [];
    private CitiesStorage $citiesStorage;

    public function __construct(CitiesStorage $citiesStorage)
    {
        $this->citiesStorage = $citiesStorage;
    }

    public function getPostalCodeCities(string $postalCode): array
    {
        return $this->findCitiesForPostalCode($this->getCitiesList(), $postalCode);
    }

    public function getCity(string $postalCode, ?string $inseeCode): ?string
    {
        if ($inseeCode) {
            $citiesList = $this->getCitiesList();

            return $citiesList[ltrim($inseeCode, '0')]['postal_code'] === $postalCode ? $citiesList[ltrim($inseeCode, '0')]['name'] : null;
        }

        return null;
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
                $city['postal_code'] === $postalCode
                && str_starts_with($this->canonicalizeCityName($city['name']), $normalizedName)
            ) {
                return $inseeCode;
            }
        }

        return null;
    }

    public function getCountryISOCode(string $postalCode): string
    {
        foreach (CitiesStorage::$countries as $prefix => $country) {
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
                if (0 !== strpos($city['postal_code'], $search)) {
                    continue;
                }
            }

            $results[$inseeCode] = array_merge($city, ['insee_code' => $inseeCode]);

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
     * @param ReferentTag[]|array $tags
     */
    public function searchCitiesForTags(array $tags, string $search, int $maxResults = 10): array
    {
        $filters = [];
        foreach ($tags as $tag) {
            if (AreaUtils::PREFIX_POSTALCODE_CORSICA === $tag->getCode()) {
                $filters[] = AreaUtils::CODE_CORSICA_A;
                $filters[] = AreaUtils::CODE_CORSICA_B;
            } elseif ($tag->isDepartmentTag() || $tag->isBoroughTag()) {
                $filters[] = $tag->getCode();
            } elseif ($code = $tag->getDepartmentCodeFromCirconscriptionName()) {
                $filters[] = $code;
            }
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
        $citiesList = $this->getCitiesList();
        $trimmedInseeCode = ltrim($inseeCode, '0');
        if (\array_key_exists($inseeCode, $citiesList) || \array_key_exists($trimmedInseeCode, $citiesList)) {
            $city = $citiesList[$inseeCode] ?? $citiesList[$trimmedInseeCode];

            return array_merge($city, ['insee_code' => $inseeCode]);
        }

        return null;
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
            if ($city['postal_code'] === $postalCode) {
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
