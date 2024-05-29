<?php

namespace App\Entity\AdherentMessage;

use App\AdherentMessage\AdherentMessageTypeEnum;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity]
class CommitteeAdherentMessage extends AbstractAdherentMessage implements CampaignAdherentMessageInterface
{
    public function getType(): string
    {
        return AdherentMessageTypeEnum::COMMITTEE;
    }
}
