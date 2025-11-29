<?php

declare(strict_types=1);

namespace App\Mailchimp\Contact;

use MyCLabs\Enum\Enum;

class ContactStatusEnum extends Enum
{
    public const CLEANED = 'cleaned';
    public const SUBSCRIBED = 'subscribed';
    public const UNSUBSCRIBED = 'unsubscribed';
}
