<?php

namespace App\Entity\AdherentMessage;

use App\AdherentMessage\AdherentMessageTypeEnum;
use App\Scope\ScopeEnum;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity
 */
class FdeCoordinatorAdherentMessage extends AbstractAdherentMessage implements CampaignAdherentMessageInterface
{
    public function getType(): string
    {
        return AdherentMessageTypeEnum::FDE_COORDINATOR;
    }

    protected function getScope(): ?string
    {
        return ScopeEnum::FDE_COORDINATOR;
    }
}
