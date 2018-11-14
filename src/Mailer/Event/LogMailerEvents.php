<?php

namespace AppBundle\Mailer\Event;

final class LogMailerEvents
{
    public const DELIVERY_MESSAGE = 'historical_mailer.delivery.message';

    private function __construct()
    {
    }
}
