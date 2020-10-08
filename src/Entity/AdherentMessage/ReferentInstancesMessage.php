<?php

namespace App\Entity\AdherentMessage;

use App\AdherentMessage\AdherentMessageTypeEnum;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity
 */
class ReferentInstancesMessage extends AbstractAdherentMessage implements TransactionalMessageInterface
{
    public function getType(): string
    {
        return AdherentMessageTypeEnum::REFERENT_INSTANCES;
    }

    public function isSynchronized(): bool
    {
        return true;
    }
}
