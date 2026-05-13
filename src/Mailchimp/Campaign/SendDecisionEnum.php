<?php

declare(strict_types=1);

namespace App\Mailchimp\Campaign;

enum SendDecisionEnum
{
    case Send;
    case Retry;
    case Abort;
}
