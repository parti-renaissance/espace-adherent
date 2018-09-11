<?php

namespace AppBundle\Coordinator\Filter;

use AppBundle\Entity\Committee;

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

    protected function getCoordinatorAreaCodes(): array
    {
        if ($this->coordinator->isCoordinatorCommitteeSector()) {
            return $this->coordinator->getCoordinatorCommitteeArea()->getCodes();
        }

        return [];
    }
}
