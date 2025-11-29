<?php

declare(strict_types=1);

namespace App\Membership\Event;

use App\Entity\Adherent;
use Symfony\Contracts\EventDispatcher\Event;

class UserEmailEvent extends Event
{
    private Adherent $user;
    private string $oldEmail;

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
