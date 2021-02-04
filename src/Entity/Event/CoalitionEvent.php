<?php

namespace App\Entity\Event;

use App\Event\EventTypeEnum;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity
 */
class CoalitionEvent extends BaseEvent
{
    public function getType(): string
    {
        return EventTypeEnum::TYPE_COALITION;
    }
}
