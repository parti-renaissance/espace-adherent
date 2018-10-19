<?php

namespace AppBundle\Twig;

use Twig\Extension\AbstractExtension;
use Twig\TwigFunction;

class EventExtension extends AbstractExtension
{
    public function getFunctions()
    {
        return [
            new TwigFunction('is_event_already_participating', [EventRuntime::class, 'isEventAlreadyParticipating']),
        ];
    }
}
