<?php

declare(strict_types=1);

namespace App\AdherentMessage;

use Symfony\Component\Uid\Uuid;

interface StaticSegmentInterface
{
    public function getUuid(): Uuid;

    public function getMailchimpId(): ?int;

    public function setMailchimpId(int $id): void;
}
