<?php

namespace App\Coordinator\Filter;

use App\Entity\Committee;

class CommitteeFilter extends AbstractCoordinatorAreaFilter
{
    protected function getAvailableStatus(): array
    {
        return [
            Committee::PENDING,
            Committee::PRE_APPROVED,
            Committee::PRE_REFUSED,
        ];
    }
}
