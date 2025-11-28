<?php

declare(strict_types=1);

namespace App\Mailer\Event;

final class MailerEvents
{
    public const BEFORE_EMAIL_BUILD = 'mailer.before.email_build';
    public const DELIVERY_MESSAGE = 'mailer.delivery.message';
    public const DELIVERY_SUCCESS = 'mailer.delivery.success';
    public const DELIVERY_ERROR = 'mailer.delivery.error';

    private function __construct()
    {
    }
}
