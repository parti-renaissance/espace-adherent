<?php

namespace App\Statistics\Acquisition\Calculator;

use App\Entity\Committee;
use App\Statistics\Acquisition\StatisticsRequest;

class PendingCommitteeCalculator extends AbstractCommitteeCalculator
{
    public static function getPriority(): int
    {
        return 17;
    }

    public function getLabel(): string
    {
        return 'Comités en attente (nouveaux)';
    }

    protected function processing(StatisticsRequest $request, array $keys): array
    {
        return $this->calculateCommitteeByStatus(Committee::PENDING, $request);
    }
}
