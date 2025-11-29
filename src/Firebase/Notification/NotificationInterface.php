<?php

declare(strict_types=1);

namespace App\Firebase\Notification;

interface NotificationInterface
{
    public function getTitle(): string;

    public function getBody(): string;

    public function getData(): array;

    public function addData(string $key, string $value): void;

    public function setTokens(array $tokens): void;

    public function setScope(?string $scope);

    public function getScope(): ?string;
}
