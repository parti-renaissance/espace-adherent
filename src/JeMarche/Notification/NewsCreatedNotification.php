<?php

namespace App\JeMarche\Notification;

use App\Entity\Jecoute\News;
use App\Firebase\Notification\AbstractTopicNotification;

class NewsCreatedNotification extends AbstractTopicNotification
{
    public static function create(News $news, string $title): self
    {
        return new self(
            $title,
            $news->getText(),
            $news->getTopic()
        );
    }
}
