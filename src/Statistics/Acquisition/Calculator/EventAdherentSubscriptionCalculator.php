<?php

namespace App\Statistics\Acquisition\Calculator;

class EventAdherentSubscriptionCalculator extends AbstractEventSubscriptionCalculator
{
    public static function getPriority(): int
    {
        return 12;
    }

    public function getLabel(): string
    {
        return 'Adhérents inscrits à des événements (total)';
    }

    protected function isAdherentOnly(): bool
    {
        return true;
    }
}
