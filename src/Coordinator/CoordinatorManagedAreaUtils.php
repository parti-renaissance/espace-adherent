<?php

namespace App\Coordinator;

use App\Entity\CitizenProject;
use App\Utils\AreaUtils;

class CoordinatorManagedAreaUtils extends AreaUtils
{
    public static function getCodeFromCitizenProject(CitizenProject $citizenProject): string
    {
        if (self::CODE_FRANCE === $citizenProject->getCountry()) {
            return static::getCodeFromPostalCode($citizenProject->getPostalCode());
        }

        return static::getCodeFromCountry($citizenProject->getCountry());
    }
}
