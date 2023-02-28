<?php

namespace App\Mailer\Message\Procuration;

use App\Mailer\Message\Message;

abstract class AbstractProcurationMessage extends Message
{
    protected static function updateSenderInfo(Message $message): Message
    {
        $message->setSenderEmail('ne-pas-repondre@parti-renaissance.fr');
        $message->setSenderName('Procuration Renaissance');

        return $message;
    }
}
