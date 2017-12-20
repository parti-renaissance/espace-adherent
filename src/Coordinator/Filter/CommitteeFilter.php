<?php

namespace AppBundle\Coordinator\Filter;

use AppBundle\Coordinator\CoordinatorAreaSectors;
use AppBundle\Entity\Committee;
use AppBundle\Entity\CoordinatorManagedArea;

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
        return $this->coordinator->getCoordinatorManagedAreas()->filter(function (CoordinatorManagedArea $area) {
            return CoordinatorAreaSectors::COMMITTEE_SECTOR === $area->getSector();
        })->first()->getCodes();
    }
}
