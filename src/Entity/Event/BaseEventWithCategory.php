<?php

namespace App\Entity\Event;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation\Groups;

abstract class BaseEventWithCategory extends BaseEvent
{
    /**
     * @var EventCategoryInterface|EventCategory|null
     *
     * @ORM\ManyToOne(targetEntity="App\Entity\Event\EventCategory")
     *
     * @Groups({"event_read", "event_list_read", "event_write"})
     */
    protected $category;
}
