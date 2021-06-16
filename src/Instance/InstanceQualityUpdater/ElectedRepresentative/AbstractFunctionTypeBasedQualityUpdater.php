<?php

namespace App\Instance\InstanceQualityUpdater\ElectedRepresentative;

use App\Entity\Adherent;
use App\Entity\UserListDefinitionEnum;

abstract class AbstractFunctionTypeBasedQualityUpdater extends AbstractElectedRepresentativeBasedUpdater
{
    protected function getMandates(Adherent $adherent): array
    {
        return $this->mandateRepository->findByFunctionAndUserListDefinitionForAdherent(
            $this->getFunctionCode(),
            UserListDefinitionEnum::CODE_ELECTED_REPRESENTATIVE_INSTANCES_MEMBER,
            $adherent
        );
    }

    abstract protected function getFunctionCode(): string;
}
