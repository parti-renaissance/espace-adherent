<?php

namespace App\Firebase\Notification;

abstract class AbstractTopicNotification extends AbstractNotification implements TopicNotificationInterface
{
    /**
     * @var string
     */
    protected $topic;

    public function __construct(string $title, string $body, string $topic)
    {
        $this->topic = $topic;

        parent::__construct($title, $body);
    }

    public function getTopic(): string
    {
        return $this->topic;
    }
}
