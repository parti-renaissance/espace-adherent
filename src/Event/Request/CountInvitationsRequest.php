<?php

namespace App\Event\Request;

use Ramsey\Uuid\Uuid;
use Ramsey\Uuid\UuidInterface;

class CountInvitationsRequest
{
    public ?UuidInterface $agora = null;
    public array $roles = [];

    public function isEmpty(): bool
    {
        return
            (empty($this->agora) || !Uuid::isValid($this->agora))
            && empty($this->roles);
    }
}
