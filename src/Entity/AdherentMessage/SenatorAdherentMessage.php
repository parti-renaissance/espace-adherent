<?php

namespace App\Entity\AdherentMessage;

use App\AdherentMessage\AdherentMessageTypeEnum;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity
 */
class SenatorAdherentMessage extends AbstractAdherentMessage
{
    public function getType(): string
    {
        return AdherentMessageTypeEnum::SENATOR;
    }
}
