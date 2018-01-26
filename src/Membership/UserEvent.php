<?php

namespace AppBundle\Membership;

use AppBundle\Entity\Adherent;
use Symfony\Component\EventDispatcher\Event;

class UserEvent extends Event
{
    private $user;

    public function __construct(Adherent $adherent)
    {
        $this->user = $adherent;
    }

    public function getUser(): Adherent
    {
        return $this->user;
    }
}
