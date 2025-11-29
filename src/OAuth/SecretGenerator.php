<?php

declare(strict_types=1);

namespace App\OAuth;

class SecretGenerator
{
    public static function generate(int $length = 32): string
    {
        return base64_encode(random_bytes($length));
    }
}
