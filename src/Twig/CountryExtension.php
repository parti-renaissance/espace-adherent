<?php

declare(strict_types=1);

namespace App\Twig;

use Symfony\Component\Intl\Countries;
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
        return Countries::getName($countryCode);
    }
}
