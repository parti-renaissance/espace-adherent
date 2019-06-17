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
            new TwigFunction('get_city_name_from_insee_code', [__CLASS__, 'getCityNameFromInseeCode']),
            new TwigFunction('get_city_data_from_insee_code', [__CLASS__, 'getCityDataFromInseeCode']),
        ];
    }

    public static function getUnitedNationsList(array $context): array
    {
        return UnitedNationsBundle::getCountries($context['app']->getRequest()->getLocale());
    }

    public static function getCityNameFromInseeCode(string $inseeCode): string
    {
        if ($city = FranceCitiesBundle::getCityDataFromInseeCode($inseeCode)) {
            return $city['name'];
        }

        return $inseeCode;
    }

    public static function getCityDataFromInseeCode(string $inseeCode): ?array
    {
        return FranceCitiesBundle::getCityDataFromInseeCode($inseeCode);
    }
}
