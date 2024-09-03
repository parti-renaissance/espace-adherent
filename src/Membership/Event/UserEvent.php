<?php

namespace App\Membership\Event;

use App\Entity\Adherent;
use Symfony\Contracts\EventDispatcher\Event;

class UserEvent extends Event
{
    private Adherent $user;
    private ?bool $allowEmailNotifications;
    private ?bool $allowMobileNotifications;

    public function __construct(
        Adherent $adherent,
        ?bool $allowEmailNotifications = null,
        ?bool $allowMobileNotifications = null,
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
