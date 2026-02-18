<?php

declare(strict_types=1);

namespace App\AdherentMessage;

use App\Entity\AdherentMessage\SegmentFilterInterface;
use Ramsey\Uuid\UuidInterface;

interface DynamicSegmentInterface
{
    public function getUuid(): UuidInterface;

    public function getMailchimpId(): ?int;

    public function setMailchimpId(int $id): void;

    public function getRecipientCount(): ?int;

    public function setRecipientCount(?int $recipientCount): void;

    public function isSynchronized(): bool;

    public function setSynchronized(bool $synchronized): void;

    public function getFilter(): ?SegmentFilterInterface;
}
