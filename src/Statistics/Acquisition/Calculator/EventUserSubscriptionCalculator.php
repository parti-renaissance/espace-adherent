<?php

namespace App\Statistics\Acquisition\Calculator;

class EventUserSubscriptionCalculator extends AbstractEventSubscriptionCalculator
{
    public function getLabel(): string
    {
        return 'Non-adhérents inscrits à des événements (total)';
    }

    protected function isAdherentOnly(): bool
    {
        return false;
    }
}
