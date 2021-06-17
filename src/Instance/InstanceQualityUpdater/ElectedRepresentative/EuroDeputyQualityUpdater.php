<?php

namespace App\Instance\InstanceQualityUpdater\ElectedRepresentative;

use App\Entity\ElectedRepresentative\MandateTypeEnum;
use App\Instance\InstanceQualityEnum;

class EuroDeputyQualityUpdater extends AbstractMandateTypeBasedQualityUpdater
{
    protected function getMandateTypes(): array
    {
        return [MandateTypeEnum::EURO_DEPUTY];
    }

    protected function getQuality(): string
    {
        return InstanceQualityEnum::EUROPEAN_DEPUTY;
    }
}
