<?php

namespace App\Firebase\Notification;

class AbstractMulticastNotification extends AbstractNotification implements MulticastNotificationInterface
{
    /**
     * @var array
     */
    protected $tokens;

    public function __construct(string $title, string $body, array $tokens)
    {
        $this->tokens = $tokens;

        parent::__construct($title, $body);
    }

    public function getTokens(): array
    {
        return $this->tokens;
    }
}
