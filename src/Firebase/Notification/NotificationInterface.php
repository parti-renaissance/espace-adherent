<?php

namespace App\Firebase\Notification;

interface NotificationInterface
{
    public function getTitle(): string;

    public function getBody(): string;

    public function getData(): array;

    public function addData(string $key, string $value): void;
}
