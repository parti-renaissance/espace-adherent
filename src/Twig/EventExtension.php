<?php

namespace App\Twig;

use Twig\Extension\AbstractExtension;
use Twig\TwigFilter;
use Twig\TwigFunction;

class EventExtension extends AbstractExtension
{
    public function getFunctions()
    {
        return [
            new TwigFunction('is_event_already_participating', [EventRuntime::class, 'isEventAlreadyParticipating']),
        ];
    }

    public function getFilters()
    {
        return [
            new TwigFilter('utc_offset', [EventRuntime::class, 'offsetTimeZone']),
        ];
    }
}
