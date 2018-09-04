<?php

namespace AppBundle\Statistics\Acquisition\Calculator;

use AppBundle\Statistics\Acquisition\StatisticsRequest;

class RatioCommitteeMemberAdherentCalculator extends AbstractCalculator
{
    private $adherentCalculator;
    private $committeeMemberCalculator;

    public function __construct(AdherentCalculator $adherentCalculator, CommitteeMemberCalculator $committeeMemberCalculator)
    {
        $this->adherentCalculator = $adherentCalculator;
        $this->committeeMemberCalculator = $committeeMemberCalculator;
    }

    public function getLabel(): string
    {
        return 'Ratio membre de comité par nbr adhérents (total)';
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
