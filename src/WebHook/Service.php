<?php

namespace App\WebHook;

use MyCLabs\Enum\Enum;

/**
 * @method static Service MAILCHIMP()
 */
final class Service extends Enum
{
    public const MAILCHIMP = 'mailchimp';

    public const SERVICES = [
        self::MAILCHIMP,
    ];
}
