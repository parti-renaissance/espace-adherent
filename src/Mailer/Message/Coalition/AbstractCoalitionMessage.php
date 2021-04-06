<?php

namespace App\Mailer\Message\Coalition;

use App\Mailer\Message\Message;

abstract class AbstractCoalitionMessage extends Message
{
    protected static function updateSenderInfo(Message $message): Message
    {
        $message->setSenderEmail('contact@pourunecause.fr');
        $message->setSenderName('Pour une cause');

        return $message;
    }
}
