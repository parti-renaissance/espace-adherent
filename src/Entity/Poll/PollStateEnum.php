<?php

declare(strict_types=1);

namespace App\Entity\Poll;

enum PollStateEnum: string
{
    public const UPCOMING = 'upcoming';
    public const IN_PROGRESS = 'in_progress';
    public const FINISHED = 'finished';

    public const ALL = [
        self::UPCOMING,
        self::IN_PROGRESS,
        self::FINISHED,
    ];
}
