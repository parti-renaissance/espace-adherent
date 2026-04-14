<?php

declare(strict_types=1);

namespace App\OAuth\Oidc;

class ValidatedIdTokenHint
{
    public function __construct(
        public readonly string $userUuid,
        public readonly string $clientUuid,
    ) {
    }
}
