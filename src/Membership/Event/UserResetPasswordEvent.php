<?php

declare(strict_types=1);

namespace App\Membership\Event;

use App\Entity\Adherent;
use App\Entity\AdherentResetPasswordToken;
use Symfony\Contracts\EventDispatcher\Event;

class UserResetPasswordEvent extends Event
{
    private Adherent $user;
    private AdherentResetPasswordToken $resetPasswordToken;
    private ?string $source;

    public function __construct(
        Adherent $adherent,
        AdherentResetPasswordToken $resetPasswordToken,
        ?string $source = null,
    ) {
        $this->user = $adherent;
        $this->resetPasswordToken = $resetPasswordToken;
        $this->source = $source;
    }

    public function getUser(): Adherent
    {
        return $this->user;
    }

    public function getResetPasswordToken(): AdherentResetPasswordToken
    {
        return $this->resetPasswordToken;
    }

    public function getSource(): ?string
    {
        return $this->source;
    }
}
