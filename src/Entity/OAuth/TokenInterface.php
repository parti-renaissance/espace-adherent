<?php

declare(strict_types=1);

namespace App\Entity\OAuth;

interface TokenInterface
{
    public function isExpired(): bool;

    public function isRevoked(): bool;
}
