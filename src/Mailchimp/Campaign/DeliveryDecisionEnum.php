<?php

declare(strict_types=1);

namespace App\Mailchimp\Campaign;

enum DeliveryDecisionEnum
{
    case Ok;
    case Pending;
    case Failed;
    case Unverifiable;
    case StillSending;
    case NotSending;
}
