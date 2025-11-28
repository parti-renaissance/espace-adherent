<?php

namespace App\Event\Request;

use Ramsey\Uuid\UuidInterface;

class CountInvitationsRequest
{
    public ?UuidInterface $agora = null;
    public array $roles = [];

    public function __construct(
        public readonly array $zones,
        public readonly array $committeeUuids,
        public readonly array $agoraUuids,
    ) {
    }

    public function isEmpty(): bool
    {
        return empty($this->agora) && empty($this->roles);
    }

    public function hasPerimeter(): bool
    {
        return !empty($this->zones) || !empty($this->committeeUuids) || !empty($this->agoraUuids);
    }
}
