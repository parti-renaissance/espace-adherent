<?php

namespace AppBundle\Statistics\Acquisition\Calculator;

use AppBundle\Entity\Committee;
use AppBundle\Statistics\Acquisition\StatisticsRequest;

class ApprovedCommitteeCalculator extends AbstractCommitteeCalculator
{
    public function getLabel(): string
    {
        return 'Comités (nouveaux)';
    }

    protected function processing(StatisticsRequest $request, array $keys): array
    {
        return $this->calculateCommitteeByStatus(Committee::APPROVED, $request);
    }
}
