<?php

declare(strict_types=1);

namespace App\JeMengage\Push\Command;

use App\Entity\Jecoute\News;

class NewsCreatedNotificationCommand extends AbstractSendNotificationCommand
{
    public function getClass(): string
    {
        return News::class;
    }
}
