<?php

namespace App\JeMarche\Command;

use App\Entity\Jecoute\News;

class NewsCreatedNotificationCommand extends AbstractSendNotificationCommand
{
    public function getClass(): string
    {
        return News::class;
    }
}
