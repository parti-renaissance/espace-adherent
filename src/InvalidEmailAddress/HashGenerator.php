<?php

declare(strict_types=1);

namespace App\InvalidEmailAddress;

class HashGenerator
{
    private string $secret;

    public function __construct(string $invalidEmailHashKey)
    {
        $this->secret = $invalidEmailHashKey;
    }

    public function generate(string $email): string
    {
        return hash_hmac('sha256', mb_strtolower($email), $this->secret);
    }
}
