<?php

namespace App\Entity\AdherentMessage;

use App\AdherentMessage\AdherentMessageTypeEnum;
use App\Scope\ScopeEnum;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity
 */
class CorrespondentAdherentMessage extends AbstractAdherentMessage implements CampaignAdherentMessageInterface
{
    public function getType(): string
    {
        return AdherentMessageTypeEnum::CORRESPONDENT;
    }

    protected function getScope(): ?string
    {
        return ScopeEnum::CORRESPONDENT;
    }
}
