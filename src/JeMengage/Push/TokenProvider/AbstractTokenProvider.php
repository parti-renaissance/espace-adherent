<?php

namespace App\JeMengage\Push\TokenProvider;

use App\Repository\PushTokenRepository;

abstract class AbstractTokenProvider implements TokenProviderInterface
{
    public function __construct(protected readonly PushTokenRepository $pushTokenRepository)
    {
    }

    public static function getDefaultPriority(): int
    {
        return 0;
    }
}
