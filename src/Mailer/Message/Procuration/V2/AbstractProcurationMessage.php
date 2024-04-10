<?php

namespace App\Mailer\Message\Procuration\V2;

use App\Mailer\Message\Message;

abstract class AbstractProcurationMessage extends Message
{
    protected const SENDER_NAME = 'Procurations • Besoin d\'Europe';
    protected const SENDER_EMAIL = 'procuration@besoindeurope.fr';
    protected const SENDER_EMAIL_NO_REPLY = 'ne-pas-repondre@besoindeurope.fr';

    protected static function updateSenderInfo(Message $message): Message
    {
        $message->setSenderEmail(static::SENDER_EMAIL);
        $message->setSenderName(static::SENDER_NAME);

        return $message;
    }

    protected static function updateNoReplySenderInfo(Message $message): Message
    {
        $message->setSenderEmail(static::SENDER_EMAIL_NO_REPLY);
        $message->setSenderName(static::SENDER_NAME);

        return $message;
    }
}
