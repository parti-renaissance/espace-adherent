<?php

namespace App\Entity\AdherentMessage;

use App\AdherentMessage\AdherentMessageTypeEnum;
use App\Scope\ScopeEnum;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity]
class PresidentDepartmentalAssemblyAdherentMessage extends AbstractAdherentMessage implements CampaignAdherentMessageInterface
{
    public function getType(): string
    {
        return AdherentMessageTypeEnum::PRESIDENT_DEPARTMENTAL_ASSEMBLY;
    }

    protected function getScope(): ?string
    {
        return ScopeEnum::PRESIDENT_DEPARTMENTAL_ASSEMBLY;
    }
}
