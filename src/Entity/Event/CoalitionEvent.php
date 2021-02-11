<?php

namespace App\Entity\Event;

use App\Entity\Coalition\Coalition;
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

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\Coalition\Coalition", inversedBy="events")
     */
    private $coalition;

    public function getCoalition(): ?Coalition
    {
        return $this->coalition;
    }

    public function setCoalition(Coalition $coalition): void
    {
        $this->coalition = $coalition;
    }
}
