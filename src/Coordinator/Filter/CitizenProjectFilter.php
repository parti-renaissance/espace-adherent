<?php

namespace AppBundle\Coordinator\Filter;

use AppBundle\Coordinator\CoordinatorAreaSectors;
use AppBundle\Entity\CitizenProject;
use AppBundle\Entity\CoordinatorManagedArea;

class CitizenProjectFilter extends AbstractCoordinatorAreaFilter
{
    protected function getAvailableStatus(): array
    {
        return [
            CitizenProject::PENDING,
            CitizenProject::PRE_APPROVED,
            CitizenProject::PRE_REFUSED,
            CitizenProject::APPROVED,
        ];
    }

    protected function getCoordinatorAreaCodes(): array
    {
        return $this->coordinator->getCoordinatorManagedAreas()->filter(function (CoordinatorManagedArea $area) {
            return CoordinatorAreaSectors::CITIZEN_PROJECT_SECTOR === $area->getSector();
        })->first()->getCodes();
    }
}
