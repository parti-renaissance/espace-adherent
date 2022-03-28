<?php

namespace App\Entity;

interface EntityScopeVisibilityInterface
{
    public function getVisibility(): string;

    public function isNationalVisibility(): bool;
}
