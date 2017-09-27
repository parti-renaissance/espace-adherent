<?php

namespace AppBundle\Mailer\Event;

final class MailerEvents
{
    const DELIVERY_MESSAGE = 'mailer.delivery.message';
    const DELIVERY_SUCCESS = 'mailer.delivery.success';
    const DELIVERY_ERROR = 'mailer.delivery.error';

    private function __construct()
    {
    }
}
