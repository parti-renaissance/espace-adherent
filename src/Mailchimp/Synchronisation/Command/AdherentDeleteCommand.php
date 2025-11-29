<?php

declare(strict_types=1);

namespace App\Mailchimp\Synchronisation\Command;

use App\Mailchimp\SynchronizeMessageInterface;

class AdherentDeleteCommand implements SynchronizeMessageInterface
{
    private string $email;
    private ?int $adherentId;

    public function __construct(string $email, ?int $adherentId = null)
    {
        $this->email = $email;
        $this->adherentId = $adherentId;
    }

    public function getEmail(): string
    {
        return $this->email;
    }

    public function getAdherentId(): ?int
    {
        return $this->adherentId;
    }
}
