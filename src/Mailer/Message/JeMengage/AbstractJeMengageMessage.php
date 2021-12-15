<?php

namespace App\Mailer\Message\JeMengage;

use App\Mailer\Message\Message;

abstract class AbstractJeMengageMessage extends Message
{
    protected static function updateSenderInfo(Message $message): Message
    {
        $message->setSenderEmail('ne-pas-repondre@je-mengage.fr');
        $message->setSenderName('Je-mengage.fr');

        return $message;
    }
}
