<?php

namespace AppBundle\Twig;

use AppBundle\Intl\FranceCitiesBundle;
use AppBundle\Intl\UnitedNationsBundle;
use Twig\Extension\AbstractExtension;
use Twig\TwigFunction;

class AppIntlExtension extends AbstractExtension
{
    public function getFunctions(): array
    {
        return [
            new TwigFunction('list_united_nations', [__CLASS__, 'getUnitedNationsList'], [
                'needs_context' => true,
            ]),
            new TwigFunction('get_cities_names_from_insee_code', [__CLASS__, 'getCitiesNamesFromInseeCode']),
            new TwigFunction('get_city_name_from_insee_code', [__CLASS__, 'getCityNameFromInseeCode']),
            new TwigFunction('get_city_data_from_insee_code', [__CLASS__, 'getCityDataFromInseeCode']),
        ];
    }

    public static function getUnitedNationsList(array $context): array
    {
        return UnitedNationsBundle::getCountries($context['app']->getRequest()->getLocale());
    }

    public static function getCitiesNamesFromInseeCode(array $inseeCodes, bool $sort = true): array
    {
        $cities = [];

        foreach ($inseeCodes as $code) {
            $city = self::getCityNameFromInseeCode($code);

            if ($city !== $code) {
                $cities[] = $city;
            }
        }

        if ($sort) {
            asort($cities);
        }

        return $cities;
    }

    public static function getCityNameFromInseeCode(string $inseeCode): string
    {
        $city = FranceCitiesBundle::getCityDataFromInseeCode($inseeCode);

        if ($city && array_key_exists('name', $city)) {
            return $city['name'];
        }

        return $inseeCode;
    }

    public static function getCityDataFromInseeCode(string $inseeCode): ?array
    {
        return FranceCitiesBundle::getCityDataFromInseeCode($inseeCode);
    }
}
