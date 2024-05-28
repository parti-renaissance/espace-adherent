<?php

namespace App\Entity\Report;

use App\Entity\Event\BaseEvent;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity]
class CommunityEventReport extends Report
{
    /**
     * @var BaseEvent
     */
    #[ORM\JoinColumn(name: 'community_event_id', onDelete: 'CASCADE')]
    #[ORM\ManyToOne(targetEntity: BaseEvent::class)]
    protected $subject;
}
