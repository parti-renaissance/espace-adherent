<?php

namespace App\Coordinator\Filter;

use App\Entity\CitizenProject;

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
        if ($this->coordinator->isCoordinatorCitizenProjectSector()) {
            return $this->coordinator->getCoordinatorCitizenProjectArea()->getCodes();
        }

        return [];
    }
}
