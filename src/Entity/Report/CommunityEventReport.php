<?php

namespace App\Entity\Report;

use App\Entity\Event\CommitteeEvent;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity
 */
class CommunityEventReport extends Report
{
    /**
     * @var CommitteeEvent
     *
     * @ORM\ManyToOne(targetEntity="App\Entity\Event\CommitteeEvent")
     * @ORM\JoinColumn(name="community_event_id")
     */
    protected $subject;
}
