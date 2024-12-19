<?php

namespace App\JeMengage\Push\Command;

use App\Entity\Jecoute\News;

class NewsCreatedNotificationCommand extends AbstractSendNotificationCommand
{
    public function getClass(): string
    {
        return News::class;
    }
}
