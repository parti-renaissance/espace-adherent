<?php

namespace App\AdherentMessage;

use Ramsey\Uuid\UuidInterface;

interface StaticSegmentInterface
{
    public function getUuid(): UuidInterface;

    public function getMailchimpId(): ?int;

    public function setMailchimpId(int $id): void;
}
