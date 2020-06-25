<?php

namespace App\AdherentCharter;

use App\Entity\AdherentCharter\AdherentCharterInterface;
use App\Entity\AdherentCharter\DeputyCharter;
use App\Entity\AdherentCharter\MunicipalChiefCharter;
use App\Entity\AdherentCharter\ReferentCharter;
use App\Entity\AdherentCharter\SenatorCharter;
use App\Entity\AdherentCharter\SenatorialCandidateCharter;

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
            case AdherentCharterTypeEnum::TYPE_SENATORIAL_CANDIDATE:
                return new SenatorialCandidateCharter();
        }

        return null;
    }
}
