<?php

namespace App\Membership\Event;

use App\Entity\Adherent;
use Symfony\Contracts\EventDispatcher\Event;

class UserEvent extends Event
{
    private Adherent $user;
    private ?bool $allowEmailNotifications;
    private ?bool $allowMobileNotifications;
    private bool $adminEvent;

    public function __construct(
        Adherent $adherent,
        bool $allowEmailNotifications = null,
        bool $allowMobileNotifications = null,
        bool $adminEvent = false
    ) {
        $this->user = $adherent;
        $this->allowEmailNotifications = $allowEmailNotifications;
        $this->allowMobileNotifications = $allowMobileNotifications;
        $this->adminEvent = $adminEvent;
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

    public function isAdminEvent(): bool
    {
        return $this->adminEvent;
    }
}
