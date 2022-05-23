<?php

namespace App\Twig;

use Twig\Extension\AbstractExtension;
use Twig\TwigFunction;

class AppIntlExtension extends AbstractExtension
{
    public function getFunctions(): array
    {
        return [
            new TwigFunction('get_city_data_from_insee_code', [AppIntlRuntime::class, 'getCityByInseeCode']),
        ];
    }
}
