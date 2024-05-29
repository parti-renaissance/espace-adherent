<?php

namespace App\Entity\AdherentMessage;

use App\AdherentMessage\AdherentMessageTypeEnum;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity]
class StatutoryAdherentMessage extends AbstractAdherentMessage implements TransactionalMessageInterface
{
    public function getType(): string
    {
        return AdherentMessageTypeEnum::STATUTORY;
    }

    public function isSynchronized(): bool
    {
        return true;
    }
}
