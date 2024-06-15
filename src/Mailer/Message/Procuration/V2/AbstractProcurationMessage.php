<?php

namespace App\Mailer\Message\Procuration\V2;

use App\Mailer\Message\Message;

abstract class AbstractProcurationMessage extends Message
{
    protected const SENDER_NAME = 'Procurations • ENSEMBLE';
    protected const SENDER_EMAIL = 'procurations@parti-renaissance.fr';

    protected static function updateSenderInfo(Message $message): Message
    {
        $message->setSenderEmail(static::SENDER_EMAIL);
        $message->setSenderName(static::SENDER_NAME);

        return $message;
    }
}
