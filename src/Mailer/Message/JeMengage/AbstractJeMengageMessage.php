<?php

namespace App\Mailer\Message\JeMengage;

use App\Mailer\Message\Message;

abstract class AbstractJeMengageMessage extends Message
{
    protected static function updateSenderInfo(Message $message): Message
    {
        $message->setSenderEmail('ne-pas-repondre@parti-renaissance.fr');
        $message->setSenderName('Je m\'engage');

        return $message;
    }
}
