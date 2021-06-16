<?php

namespace App\Instance\InstanceQualityUpdater\ElectedRepresentative;

use App\Entity\ElectedRepresentative\MandateTypeEnum;
use App\Instance\InstanceQualityEnum;

class SenatorQualityUpdater extends AbstractMandateTypeBasedQualityUpdater
{
    protected function getMandateTypes(): array
    {
        return [MandateTypeEnum::SENATOR];
    }

    protected function getQuality(): string
    {
        return InstanceQualityEnum::SENATOR;
    }
}
