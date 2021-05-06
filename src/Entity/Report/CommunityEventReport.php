<?php

namespace App\Entity\Report;

use App\Entity\Event\BaseEvent;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity
 */
class CommunityEventReport extends Report
{
    /**
     * @var BaseEvent
     *
     * @ORM\ManyToOne(targetEntity="App\Entity\Event\BaseEvent")
     * @ORM\JoinColumn(name="community_event_id")
     */
    protected $subject;
}
