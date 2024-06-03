<?php

namespace App\JeMarche\Notification;

use App\Entity\Jecoute\News;
use App\Firebase\Notification\AbstractTopicNotification;

class NewsCreatedNotification extends AbstractTopicNotification
{
    public static function create(News $news, string $title): self
    {
        $notification = new self(
            $title,
            $news->getCleanedCroppedText(50),
            $news->getTopic()
        );

        $notification->setDeepLinkFromObject($news);

        return $notification;
    }
}
