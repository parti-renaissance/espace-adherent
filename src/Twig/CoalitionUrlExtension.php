<?php

namespace App\Twig;

use Twig\Extension\AbstractExtension;
use Twig\TwigFunction;

class CoalitionUrlExtension extends AbstractExtension
{
    public function getFunctions()
    {
        return [
            new TwigFunction('cause_event_link', [CoalitionUrlRuntime::class, 'getCauseEventLink']),
            new TwigFunction('coalition_event_link', [CoalitionUrlRuntime::class, 'getCoalitionEventLink']),
            new TwigFunction('cause_link', [CoalitionUrlRuntime::class, 'getCauseLink']),
        ];
    }
}
