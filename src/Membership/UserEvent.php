<?php

namespace AppBundle\Membership;

use AppBundle\Entity\Adherent;
use Symfony\Component\EventDispatcher\Event;

class UserEvent extends Event
{
    private $user;
    private $allowEmailNotifications;
    private $allowMobileNotifications;

    public function __construct(
        Adherent $adherent,
        bool $allowEmailNotifications = null,
        bool $allowMobileNotifications = null
    ) {
        $this->user = $adherent;
        $this->allowEmailNotifications = $allowEmailNotifications;
        $this->allowMobileNotifications = $allowMobileNotifications;
    }

    public function getUser(): Adherent
    {
        return $this->user;
    }

    public function allowEmailNotifications(): ?bool
    {
        return $this->allowEmailNotifications;
    }

    public function allowMobileNotifications(): ?bool
    {
        return $this->allowMobileNotifications;
    }
}
