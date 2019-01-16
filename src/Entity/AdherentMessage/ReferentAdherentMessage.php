<?php

namespace AppBundle\Entity\AdherentMessage;

use AppBundle\AdherentMessage\AdherentMessageTypeEnum;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity
 */
class ReferentAdherentMessage extends AbstractAdherentMessage
{
    public function getType(): string
    {
        return AdherentMessageTypeEnum::REFERENT;
    }

    public function getFromName(): ?string
    {
        $name = parent::getFromName();

        return $name ? $name.' [Référent]' : null;
    }
}
