<?php

namespace App\Entity\AdherentMessage;

use App\AdherentMessage\AdherentMessageTypeEnum;
use App\Scope\ScopeEnum;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity]
class RegionalDelegateAdherentMessage extends AbstractAdherentMessage implements CampaignAdherentMessageInterface
{
    public function getType(): string
    {
        return AdherentMessageTypeEnum::REGIONAL_DELEGATE;
    }

    protected function getScope(): ?string
    {
        return ScopeEnum::REGIONAL_DELEGATE;
    }
}
