<?php

declare(strict_types=1);

namespace App\Mailchimp\Campaign\Audience;

enum AudienceCheckEnum: string
{
    case Match = 'match';
    case Drift = 'drift';
    case Mismatch = 'mismatch';
}
