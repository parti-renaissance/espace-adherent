<?php

declare(strict_types=1);

namespace App\HttpClient\GoogleAuth;

interface IdTokenProviderInterface
{
    /**
     * Returns a Google ID token valid for the given audience (a Google IAM-protected service URL).
     *
     * @throws IdTokenException when no valid token can be obtained
     */
    public function getIdToken(string $audience): string;
}
