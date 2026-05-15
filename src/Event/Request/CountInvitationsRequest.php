<?php

declare(strict_types=1);

namespace App\Event\Request;

use Symfony\Component\Uid\Uuid;

class CountInvitationsRequest
{
    public ?Uuid $agora = null;
    public ?Uuid $committee = null;
    public array $roles = [];

    public function __construct(
        public readonly array $zones,
        public readonly array $committeeUuids,
        public readonly array $agoraUuids,
    ) {
    }

    public function isEmpty(): bool
    {
        return empty($this->agora) && empty($this->committee) && empty($this->roles);
    }

    public function hasPerimeter(): bool
    {
        return !empty($this->zones) || !empty($this->committeeUuids) || !empty($this->agoraUuids);
    }
}
