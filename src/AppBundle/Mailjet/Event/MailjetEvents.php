<?php

namespace AppBundle\Mailjet\Event;

final class MailjetEvents
{
    const DELIVERY_MESSAGE = 'mailjet.delivery.message';
    const DELIVERY_SUCCESS = 'mailjet.delivery.success';
    const DELIVERY_ERROR = 'mailjet.delivery.error';

    private function __construct()
    {
    }
}
