<?php

namespace App\Twig;

use Symfony\Component\Intl\Intl;
use Twig\Extension\AbstractExtension;
use Twig\TwigFilter;

class CountryExtension extends AbstractExtension
{
    public function getFilters()
    {
        return [
            new TwigFilter('countryName', [$this, 'getCountryNameByCode']),
        ];
    }

    public function getCountryNameByCode(string $countryCode): ?string
    {
        return Intl::getRegionBundle()->getCountryName($countryCode);
    }
}
