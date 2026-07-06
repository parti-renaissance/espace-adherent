<?php

declare(strict_types=1);

namespace App\Ses\Webhook;

enum OpenReliability: string
{
    case Reliable = 'reliable';
    case Unreliable = 'unreliable';
    case Unknown = 'unknown';
}
