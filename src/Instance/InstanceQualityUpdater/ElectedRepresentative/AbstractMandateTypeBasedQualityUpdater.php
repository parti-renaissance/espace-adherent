<?php

namespace App\Instance\InstanceQualityUpdater\ElectedRepresentative;

use App\Entity\Adherent;
use App\Entity\UserListDefinitionEnum;

abstract class AbstractMandateTypeBasedQualityUpdater extends AbstractElectedRepresentativeBasedUpdater
{
    protected function getMandates(Adherent $adherent): array
    {
        return $this->mandateRepository->findByTypesAndUserListDefinitionForAdherent(
            $this->getMandateTypes(),
            UserListDefinitionEnum::CODE_ELECTED_REPRESENTATIVE_INSTANCES_MEMBER,
            $adherent
        );
    }

    abstract protected function getMandateTypes(): array;
}
