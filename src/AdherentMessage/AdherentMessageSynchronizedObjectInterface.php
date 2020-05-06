<?php

namespace App\AdherentMessage;

interface AdherentMessageSynchronizedObjectInterface
{
    public function isSynchronized(): bool;

    public function setSynchronized(bool $value): void;

    public function getExternalId(): ?string;
}
