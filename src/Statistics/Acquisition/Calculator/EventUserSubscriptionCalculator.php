<?php

namespace AppBundle\Statistics\Acquisition\Calculator;

class EventUserSubscriptionCalculator extends AbstractEventSubscriptionCalculator
{
    public function getLabel(): string
    {
        return 'Non-adherents inscrits à des événements (total)';
    }

    protected function isAdherentOnly(): bool
    {
        return false;
    }
}
