<?php

namespace AppBundle\CitizenAction;

use AppBundle\Entity\CitizenAction;
use Symfony\Component\EventDispatcher\Event;

class CitizenActionEvent extends Event
{
    private $action;

    public function __construct(CitizenAction $action)
    {
        $this->action = $action;
    }

    public function getCitizenAction(): CitizenAction
    {
        return $this->action;
    }
}
