<?php

namespace AppBundle\Referent;

use AppBundle\Entity\Committee;
use AppBundle\Utils\AreaUtils;

class ManagedAreaUtils extends AreaUtils
{
    public static function getCodeFromCommittee(Committee $committee): ?string
    {
        if (self::CODE_FRANCE === $committee->getCountry()) {
            return static::getCodeFromPostalCode($committee->getPostalCode());
        }

        return static::getCodeFromCountry($committee->getCountry());
    }
}
