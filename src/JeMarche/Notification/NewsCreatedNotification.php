<?php

namespace App\JeMarche\Notification;

use App\Entity\Jecoute\News;
use App\Firebase\Notification\AbstractMulticastNotification;

class NewsCreatedNotification extends AbstractMulticastNotification
{
    public static function create(News $news): self
    {
        $notification = new self(
            $news->getTitle(),
            $news->getCleanedCroppedText(50),
        );

        $notification->setDeepLinkFromObject($news);

        return $notification;
    }
}
