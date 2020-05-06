<?php

namespace App\Statistics\Acquisition\Calculator;

use App\Statistics\Acquisition\Calculator\Category\AdhesionCategoryTrait;
use App\Statistics\Acquisition\StatisticsRequest;

class RatioCommitteeMemberAdherentCalculator extends AbstractCalculator
{
    use AdhesionCategoryTrait;

    private $adherentCalculator;
    private $committeeMemberCalculator;

    public function __construct(
        AdherentCalculator $adherentCalculator,
        CommitteeMemberCalculator $committeeMemberCalculator
    ) {
        $this->adherentCalculator = $adherentCalculator;
        $this->committeeMemberCalculator = $committeeMemberCalculator;
    }

    public function getLabel(): string
    {
        return 'Ratio membre de comité par nbr adhérents (total)';
    }

    protected function formatEachValue(array $data): array
    {
        // don't need to format these values, they already are in float
        return $data;
    }

    protected function processing(StatisticsRequest $request, array $keys): array
    {
        return $this->mergeResults(
            function ($nbAdherents, $nbMembers) { return 0 === $nbAdherents ? 0 : round($nbMembers / $nbAdherents, 2); },
            $keys,
            $this->adherentCalculator->calculate($request, $keys),
            $this->committeeMemberCalculator->calculate($request, $keys)
        );
    }
}
