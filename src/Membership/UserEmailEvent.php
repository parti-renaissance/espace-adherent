<?php

namespace App\Membership;

use App\Entity\Adherent;
use Symfony\Component\EventDispatcher\Event;

class UserEmailEvent extends Event
{
    private $user;
    private $oldEmail;

    public function __construct(Adherent $user, string $oldEmail)
    {
        $this->user = $user;
        $this->oldEmail = $oldEmail;
    }

    public function getUser(): Adherent
    {
        return $this->user;
    }

    public function getOldEmail(): string
    {
        return $this->oldEmail;
    }
}
