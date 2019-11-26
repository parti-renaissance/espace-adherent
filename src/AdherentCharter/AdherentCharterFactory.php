<?php

namespace AppBundle\AdherentCharter;

use AppBundle\Entity\AdherentCharter\AdherentCharterInterface;
use AppBundle\Entity\AdherentCharter\DeputyCharter;
use AppBundle\Entity\AdherentCharter\MunicipalChiefCharter;
use AppBundle\Entity\AdherentCharter\ReferentCharter;
use AppBundle\Entity\AdherentCharter\SenatorCharter;

abstract class AdherentCharterFactory
{
    public static function create(string $type): ?AdherentCharterInterface
    {
        switch ($type) {
            case AdherentCharterTypeEnum::TYPE_REFERENT:
                return new ReferentCharter();
            case AdherentCharterTypeEnum::TYPE_MUNICIPAL_CHIEF:
                return new MunicipalChiefCharter();
            case AdherentCharterTypeEnum::TYPE_DEPUTY:
                return new DeputyCharter();
            case AdherentCharterTypeEnum::TYPE_SENATOR:
                return new SenatorCharter();
        }

        return null;
    }
}
