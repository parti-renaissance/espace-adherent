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
        ];
    }

    public static function getUnitedNationsList(array $context): array
    {
        return UnitedNationsBundle::getCountries($context['app']->getRequest()->getLocale());
    }

    public static function getCityNameFromInseeCode(string $inseeCode): string
    {
        return FranceCitiesBundle::getCityNameFromInseeCode($inseeCode) ?? $inseeCode;
    }
}
