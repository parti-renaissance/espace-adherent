<?php

namespace App\Entity\AdherentMessage;

use App\AdherentMessage\AdherentMessageTypeEnum;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity]
class LegislativeCandidateAdherentMessage extends AbstractAdherentMessage implements CampaignAdherentMessageInterface
{
    public function getType(): string
    {
        return AdherentMessageTypeEnum::LEGISLATIVE_CANDIDATE;
    }

    protected function getFromNameSuffix(): string
    {
        return ' • Renaissance';
    }
}
