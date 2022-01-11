<?php

namespace App\Entity;

use App\Entity\Geo\Zone;

interface EntityScopeVisibilityInterface
{
    public function getVisibility(): string;

    public function isNationalVisibility(): bool;

    public function getZone(): ?Zone;
}
