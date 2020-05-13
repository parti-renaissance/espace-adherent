<?php

namespace App\Statistics\Acquisition\Calculator;

use App\Statistics\Acquisition\Calculator\Category\AdhesionCategoryTrait;
use App\Statistics\Acquisition\StatisticsRequest;

class EventSubscriptionCalculator extends AbstractCalculator
{
    use AdhesionCategoryTrait;

    private $adherentSubscriptionCalculator;
    private $userSubscriptionCalculator;

    public function __construct(
        EventAdherentSubscriptionCalculator $adherentSubscriptionCalculator,
        EventUserSubscriptionCalculator $userSubscriptionCalculator
    ) {
        $this->adherentSubscriptionCalculator = $adherentSubscriptionCalculator;
        $this->userSubscriptionCalculator = $userSubscriptionCalculator;
    }

    public function getLabel(): string
    {
        return 'Inscrits à des événements (total)';
    }

    protected function processing(StatisticsRequest $request, array $keys): array
    {
        return $this->mergeResults(
            function (int $a, int $b) { return $a + $b; },
            $keys,
            $this->adherentSubscriptionCalculator->calculate($request, $keys),
            $this->userSubscriptionCalculator->calculate($request, $keys)
        );
    }
}
