<?php

declare(strict_types=1);

namespace App\Firebase\Notification;

abstract class AbstractNotification implements NotificationInterface
{
    private string $title;
    private string $body;
    protected array $data;
    private ?string $scope = null;

    public function __construct(string $title, string $body, array $data = [])
    {
        $this->title = $title;
        $this->body = $body;
        $this->data = $data;
    }

    public function getTitle(): string
    {
        return $this->title;
    }

    public function getBody(): string
    {
        return $this->body;
    }

    public function getData(): array
    {
        return $this->data;
    }

    public function addData(string $key, string $value): void
    {
        $this->data[$key] = $value;
    }

    protected static function formatDate(\DateTimeInterface $date, string $format): string
    {
        return new \IntlDateFormatter(
            'fr_FR',
            \IntlDateFormatter::NONE,
            \IntlDateFormatter::NONE,
            $date->getTimezone(),
            \IntlDateFormatter::GREGORIAN,
            $format
        )->format($date);
    }

    public function setScope(?string $scope): void
    {
        $this->scope = $scope;
    }

    public function getScope(): ?string
    {
        return $this->scope;
    }
}
