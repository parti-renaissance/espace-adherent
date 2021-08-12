<?php

namespace App\Entity\AdherentMessage;

use App\AdherentMessage\AdherentMessageTypeEnum;
use App\Scope\ScopeEnum;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity
 */
class CandidateAdherentMessage extends AbstractAdherentMessage implements CampaignAdherentMessageInterface
{
    public function getType(): string
    {
        return AdherentMessageTypeEnum::CANDIDATE;
    }

    protected function getScope(): ?string
    {
        return ScopeEnum::CANDIDATE;
    }
}
