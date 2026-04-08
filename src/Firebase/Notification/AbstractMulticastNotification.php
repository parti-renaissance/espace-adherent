<?php

declare(strict_types=1);

namespace App\Firebase\Notification;

abstract class AbstractMulticastNotification extends AbstractNotification implements MulticastNotificationInterface
{
    protected array $tokens = [];
    private string $scope;

    public function __construct(string $title, string $body, string $scope, array $data = [])
    {
        parent::__construct($title, $body, $data);
        $this->scope = $scope;
    }

    public function getTokens(): array
    {
        return $this->tokens;
    }

    public function setTokens(array $tokens): void
    {
        $this->tokens = $tokens;
    }

    public function getScope(): string
    {
        return $this->scope;
    }
}
