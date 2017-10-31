<?php

namespace AppBundle\Group;

use AppBundle\Entity\Adherent;
use AppBundle\Entity\Group;

class GroupWasCreatedEvent extends GroupEvent
{
    private $creator;

    public function __construct(Group $group, Adherent $creator)
    {
        parent::__construct($group);

        $this->creator = $creator;
    }

    public function getCreator(): Adherent
    {
        return $this->creator;
    }
}
