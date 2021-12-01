<?php

namespace App\Membership;

use App\Entity\Adherent;
use Symfony\Contracts\EventDispatcher\Event;

class UserEvent extends Event implements UserEventInterface
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
