<?php

declare(strict_types=1);

namespace App\Ses\Webhook;

enum SesFeedbackType
{
    case HARD_BOUNCE;
    case COMPLAINT;
}
