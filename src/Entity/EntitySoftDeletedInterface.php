<?php

namespace App\Entity;

interface EntitySoftDeletedInterface
{
    public function getDeletedAt(): ?\DateTime;

    public function isDeleted(): bool;

    public function isNotDeleted(): bool;

    public function recover(): void;
}
