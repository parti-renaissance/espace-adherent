<?php

namespace App\Collection;

use App\AdherentCharter\AdherentCharterTypeEnum;
use App\Entity\AdherentCharter\AdherentCharterInterface;
use App\Entity\AdherentCharter\DeputyCharter;
use App\Entity\AdherentCharter\MunicipalChiefCharter;
use App\Entity\AdherentCharter\ReferentCharter;
use App\Entity\AdherentCharter\SenatorCharter;
use Doctrine\Common\Collections\ArrayCollection;

class AdherentCharterCollection extends ArrayCollection
{
    public function hasReferentCharterAccepted(): bool
    {
        return $this->exists(static function (int $index, AdherentCharterInterface $charter) {
            return $charter instanceof ReferentCharter;
        });
    }

    public function hasMunicipalChiefCharterAccepted(): bool
    {
        return $this->exists(static function (int $index, AdherentCharterInterface $charter) {
            return $charter instanceof MunicipalChiefCharter;
        });
    }

    public function hasDeputyCharterAccepted(): bool
    {
        return $this->exists(static function (int $index, AdherentCharterInterface $charter) {
            return $charter instanceof DeputyCharter;
        });
    }

    public function hasSenatorCharterAccepted(): bool
    {
        return $this->exists(static function (int $index, AdherentCharterInterface $charter) {
            return $charter instanceof SenatorCharter;
        });
    }

    public function hasCharterAcceptedForType(string $type): bool
    {
        switch ($type) {
            case AdherentCharterTypeEnum::TYPE_REFERENT:
                return $this->hasReferentCharterAccepted();

            case AdherentCharterTypeEnum::TYPE_MUNICIPAL_CHIEF:
                return $this->hasMunicipalChiefCharterAccepted();

            case AdherentCharterTypeEnum::TYPE_DEPUTY:
                return $this->hasDeputyCharterAccepted();

            case AdherentCharterTypeEnum::TYPE_SENATOR:
                return $this->hasSenatorCharterAccepted();
        }

        return false;
    }
}
