<?php

declare(strict_types=1);

namespace App\Twig;

use Twig\Extension\AbstractExtension;
use Twig\TwigFilter;

class DateExtension extends AbstractExtension
{
    public function getFilters()
    {
        return [
            new TwigFilter('birthdate_to_age', [DateRuntime::class, 'birthDateToAge']),
        ];
    }
}
