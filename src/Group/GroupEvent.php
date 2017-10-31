<?php

namespace AppBundle\Group;

use AppBundle\Entity\Group;
use Symfony\Component\EventDispatcher\Event;

class GroupEvent extends Event
{
    private $group;

    public function __construct(Group $group = null)
    {
        $this->group = $group;
    }

    public function getGroup()
    {
        return $this->group;
    }
}
