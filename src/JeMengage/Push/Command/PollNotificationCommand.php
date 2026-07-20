<?php

declare(strict_types=1);

namespace App\JeMengage\Push\Command;

use App\Entity\Poll\Poll;
use App\Poll\PollReminderTypeEnum;
use Symfony\Component\Uid\Uuid;

class PollNotificationCommand extends AbstractSendNotificationCommand
{
    public function __construct(Uuid $uuid, public readonly PollReminderTypeEnum $type)
    {
        parent::__construct($uuid);
    }

    public function getClass(): string
    {
        return Poll::class;
    }
}
