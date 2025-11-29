<?php

declare(strict_types=1);

namespace App\JeMengage\Push\Notification;

use App\Entity\Jecoute\News;
use App\Firebase\Notification\AbstractMulticastNotification;

class NewsCreatedNotification extends AbstractMulticastNotification
{
    public static function create(News $news): self
    {
        return new self(
            $news->getTitle(),
            $news->getCleanedCroppedText(50),
        );
    }
}
