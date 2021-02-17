<?php

namespace App\Entity\Event;

use App\Entity\Coalition\Cause;
use App\Event\EventTypeEnum;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity
 */
class CauseEvent extends BaseEvent
{
    /**
     * @var Cause|null
     *
     * @ORM\ManyToOne(targetEntity="App\Entity\Coalition\Cause", inversedBy="events")
     */
    private $cause;

    public function getType(): string
    {
        return EventTypeEnum::TYPE_CAUSE;
    }

    public function getCause(): ?Cause
    {
        return $this->cause;
    }

    public function setCause(Cause $cause): void
    {
        $this->cause = $cause;
    }
}
