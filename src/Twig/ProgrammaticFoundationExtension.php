<?php

namespace AppBundle\Twig;

use Twig\Extension\AbstractExtension;
use Twig\TwigFilter;

class ProgrammaticFoundationExtension extends AbstractExtension
{
    public function getFilters()
    {
        return [
            new TwigFilter('sort_by_position', [ProgrammaticFoundationRuntime::class, 'sortByPosition']),
        ];
    }
}
