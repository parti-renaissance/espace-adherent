<?php

namespace App\Statistics\Acquisition\Calculator;

class EventAdherentSubscriptionCalculator extends AbstractEventSubscriptionCalculator
{
    public function getLabel(): string
    {
        return 'Adhérents inscrits à des événements (total)';
    }

    protected function isAdherentOnly(): bool
    {
        return true;
    }
}
