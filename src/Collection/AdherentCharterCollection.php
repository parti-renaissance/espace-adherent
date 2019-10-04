<?php

namespace AppBundle\Collection;

use AppBundle\AdherentCharter\AdherentCharterTypeEnum;
use AppBundle\Entity\AdherentCharter\AdherentCharterInterface;
use AppBundle\Entity\AdherentCharter\DeputyCharter;
use AppBundle\Entity\AdherentCharter\MunicipalChiefCharter;
use AppBundle\Entity\AdherentCharter\ReferentCharter;
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

    public function hasCharterAcceptedForType(string $type): bool
    {
        switch ($type) {
            case AdherentCharterTypeEnum::TYPE_REFERENT:
                return $this->hasReferentCharterAccepted();

            case AdherentCharterTypeEnum::TYPE_MUNICIPAL_CHIEF:
                return $this->hasMunicipalChiefCharterAccepted();

            case AdherentCharterTypeEnum::TYPE_DEPUTY:
                return $this->hasDeputyCharterAccepted();
        }

        return false;
    }
}
