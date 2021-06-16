<?php

namespace App\Firebase\Notification;

abstract class AbstractNotification implements NotificationInterface
{
    /**
     * @var string
     */
    protected $title;

    /**
     * @var string
     */
    protected $body;

    public function __construct(string $title, string $body)
    {
        $this->title = $title;
        $this->body = $body;
    }

    public function getTitle(): string
    {
        return $this->title;
    }

    public function getBody(): string
    {
        return $this->body;
    }

    protected static function formatDate(\DateTimeInterface $date, string $format): string
    {
        return (new \IntlDateFormatter(
            'fr_FR',
            \IntlDateFormatter::NONE,
            \IntlDateFormatter::NONE,
            $date->getTimezone(),
            \IntlDateFormatter::GREGORIAN,
            $format
        ))->format($date);
    }
}
