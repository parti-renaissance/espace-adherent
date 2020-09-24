<?php

namespace App\Entity\AdherentMessage;

use App\AdherentMessage\AdherentMessageTypeEnum;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity
 */
class ReferentTerritorialCouncilMessage extends AbstractAdherentMessage implements TransactionalMessageInterface
{
    public function getType(): string
    {
        return AdherentMessageTypeEnum::REFERENT_TERRITORIAL_COUNCIL;
    }

    public function isSynchronized(): bool
    {
        return true;
    }
}
