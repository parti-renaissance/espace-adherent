<?php

namespace App\Entity\AdherentMessage;

use App\AdherentMessage\AdherentMessageTypeEnum;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity
 */
class LreManagerElectedRepresentativeMessage extends AbstractAdherentMessage implements CampaignAdherentMessageInterface
{
    public function getType(): string
    {
        return AdherentMessageTypeEnum::LRE_MANAGER_ELECTED_REPRESENTATIVE;
    }
}
