<?php

namespace App\Mailer\Event;

final class MailerEvents
{
    public const DELIVERY_MESSAGE = 'mailer.delivery.message';
    public const DELIVERY_SUCCESS = 'mailer.delivery.success';
    public const DELIVERY_ERROR = 'mailer.delivery.error';

    private function __construct()
    {
    }
}
