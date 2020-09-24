<?php

namespace App\Entity\Report;

use App\Entity\Event;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity
 */
class CommunityEventReport extends Report
{
    /**
     * @var Event
     *
     * @ORM\ManyToOne(targetEntity="App\Entity\Event")
     * @ORM\JoinColumn(name="community_event_id")
     */
    protected $subject;
}
