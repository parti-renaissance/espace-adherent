<?php

declare(strict_types=1);

namespace App\Mailchimp\Webhook;

use MyCLabs\Enum\Enum;

class EventTypeEnum extends Enum
{
    public const UPDATE_EMAIL = 'upemail';
    public const UPDATE_PROFILE = 'profile';
    public const SUBSCRIBE = 'subscribe';
    public const UNSUBSCRIBE = 'unsubscribe';
    public const CLEANED = 'cleaned';
    public const SMS_SUBSCRIBE = 'sms_subscribe';
    public const SMS_UNSUBSCRIBE = 'sms_unsubscribe';
}
