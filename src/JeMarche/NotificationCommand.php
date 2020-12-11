<?php

namespace App\JeMarche;

use Ramsey\Uuid\UuidInterface;

class NotificationCommand
{
    private $uuid;

    public function __construct(UuidInterface $uuid)
    {
        $this->uuid = $uuid;
    }

    public function getUuid(): UuidInterface
    {
        return $this->uuid;
    }
}
