<?php

namespace App\Statistics\Acquisition\Calculator;

use App\Entity\Committee;
use App\Statistics\Acquisition\StatisticsRequest;

class ApprovedCommitteeCalculator extends AbstractCommitteeCalculator
{
    public static function getPriority(): int
    {
        return 18;
    }

    public function getLabel(): string
    {
        return 'Comités (nouveaux)';
    }

    protected function processing(StatisticsRequest $request, array $keys): array
    {
        return $this->calculateCommitteeByStatus(Committee::APPROVED, $request);
    }
}
