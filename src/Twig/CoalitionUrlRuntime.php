<?php

namespace App\Twig;

use App\Coalition\CoalitionUrlGenerator;
use App\Entity\Coalition\Cause;
use App\Entity\Event\CauseEvent;
use App\Entity\Event\CoalitionEvent;
use Twig\Extension\RuntimeExtensionInterface;

class CoalitionUrlRuntime implements RuntimeExtensionInterface
{
    private $coalitionUrlGenerator;

    public function __construct(CoalitionUrlGenerator $coalitionUrlGenerator)
    {
        $this->coalitionUrlGenerator = $coalitionUrlGenerator;
    }

    public function getCauseEventLink(CauseEvent $causeEvent): string
    {
        return $this->coalitionUrlGenerator->generateCauseEventLink($causeEvent);
    }

    public function getCoalitionEventLink(CoalitionEvent $coalitionEvent): string
    {
        return $this->coalitionUrlGenerator->generateCoalitionEventLink($coalitionEvent);
    }

    public function getCauseLink(Cause $cause): string
    {
        return $this->coalitionUrlGenerator->generateCauseLink($cause);
    }
}
