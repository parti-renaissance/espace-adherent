<?php

declare(strict_types=1);

namespace App\Entity\Poll;

enum PollStateEnum: string
{
    case UPCOMING = 'upcoming';
    case IN_PROGRESS = 'in_progress';
    case FINISHED = 'finished';
}
