<?php

namespace App\Entity\AdherentMessage;

use App\AdherentMessage\AdherentMessageTypeEnum;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity
 */
class CoalitionsMessage extends AbstractAdherentMessage implements CampaignAdherentMessageInterface, CoalitionAdherentMessageInterface
{
    public function getType(): string
    {
        return AdherentMessageTypeEnum::COALITIONS;
    }

    protected function getFromNameSuffix(): string
    {
        return '';
    }
}
