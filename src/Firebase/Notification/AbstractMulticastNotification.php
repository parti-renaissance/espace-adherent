<?php

declare(strict_types=1);

namespace App\Firebase\Notification;

abstract class AbstractMulticastNotification extends AbstractNotification implements MulticastNotificationInterface
{
    protected array $tokens = [];

    public function getTokens(): array
    {
        return $this->tokens;
    }

    public function setTokens(array $tokens): void
    {
        $this->tokens = $tokens;
    }
}
