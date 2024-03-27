<?php

namespace App\Mailer\Message\Procuration\V2;

use App\Mailer\Message\Message;

abstract class AbstractProcurationMessage extends Message
{
    private const SENDER_NAME = 'Procurations • Besoin d\'Europe';
    private const SENDER_EMAIL = 'procuration@besoindeurope.fr';
    private const SENDER_EMAIL_NO_REPLY = 'ne-pas-repondre@besoindeurope.fr';

    protected static function updateSenderInfo(Message $message): Message
    {
        $message->setSenderEmail(self::SENDER_EMAIL);
        $message->setSenderName(self::SENDER_NAME);

        return $message;
    }

    protected static function updateNoReplySenderInfo(Message $message): Message
    {
        $message->setSenderEmail(self::SENDER_EMAIL_NO_REPLY);
        $message->setSenderName(self::SENDER_NAME);

        return $message;
    }
}
