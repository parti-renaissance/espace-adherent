<?php

namespace App\Entity\Report;

use App\Entity\Event\Event;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity]
class CommunityEventReport extends Report
{
    /**
     * @var Event
     */
    #[ORM\JoinColumn(name: 'community_event_id', onDelete: 'CASCADE')]
    #[ORM\ManyToOne(targetEntity: Event::class)]
    protected $subject;
}
