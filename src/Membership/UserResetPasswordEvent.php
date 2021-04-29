<?php

namespace App\Membership;

use App\Entity\Adherent;
use App\Entity\AdherentResetPasswordToken;
use Symfony\Contracts\EventDispatcher\Event;

class UserResetPasswordEvent extends Event implements UserEventInterface
{
    private $user;
    private $resetPasswordToken;

    public function __construct(Adherent $adherent, AdherentResetPasswordToken $resetPasswordToken)
    {
        $this->user = $adherent;
        $this->resetPasswordToken = $resetPasswordToken;
    }

    public function getUser(): Adherent
    {
        return $this->user;
    }

    public function getResetPasswordToken(): AdherentResetPasswordToken
    {
        return $this->resetPasswordToken;
    }
}
