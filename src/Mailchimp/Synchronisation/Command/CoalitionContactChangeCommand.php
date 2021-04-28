<?php

namespace App\Mailchimp\Synchronisation\Command;

use App\Mailchimp\SynchronizeMessageInterface;

class CoalitionContactChangeCommand implements SynchronizeMessageInterface
{
    private $email;
    private $isAdherent;

    public function __construct(string $email, bool $adherent)
    {
        $this->email = $email;
        $this->isAdherent = $adherent;
    }

    public function getEmail(): string
    {
        return $this->email;
    }

    public function isAdherent(): bool
    {
        return $this->isAdherent;
    }
}
