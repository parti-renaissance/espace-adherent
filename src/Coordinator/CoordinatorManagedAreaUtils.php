<?php

namespace AppBundle\Coordinator;

use AppBundle\Entity\CitizenProject;
use AppBundle\Utils\AreaUtils;

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
