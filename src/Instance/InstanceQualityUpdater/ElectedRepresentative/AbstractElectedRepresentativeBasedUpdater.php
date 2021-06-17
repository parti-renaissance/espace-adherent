<?php

namespace App\Instance\InstanceQualityUpdater\ElectedRepresentative;

use App\Entity\Adherent;
use App\Entity\ElectedRepresentative\Mandate;
use App\Entity\Instance\AdherentInstanceQuality;
use App\Instance\InstanceQualityUpdater\AbstractQualityUpdater;
use App\Repository\ElectedRepresentative\MandateRepository;

abstract class AbstractElectedRepresentativeBasedUpdater extends AbstractQualityUpdater
{
    protected $mandateRepository;
    /** @var Mandate|null */
    protected $mandate;

    final public function __construct(MandateRepository $mandateRepository)
    {
        $this->mandateRepository = $mandateRepository;
    }

    protected function isValid(Adherent $adherent): bool
    {
        $mandates = $this->getMandates($adherent);
        $this->mandate = null;

        if (\count($mandates) > 0) {
            $this->mandate = current($mandates);

            return true;
        }

        return false;
    }

    abstract protected function getMandates(Adherent $adherent): array;

    protected function updateNewQuality(AdherentInstanceQuality $quality): void
    {
        $quality->setZone($this->mandate->getGeoZone());
    }
}
