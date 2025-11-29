<?php

declare(strict_types=1);

namespace App\Entity;

interface EntityScopeVisibilityInterface
{
    public function getVisibility(): string;

    public function isNationalVisibility(): bool;
}
