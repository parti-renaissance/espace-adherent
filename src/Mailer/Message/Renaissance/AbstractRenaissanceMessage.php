<?php

declare(strict_types=1);

namespace App\Mailer\Message\Renaissance;

use App\Mailer\Message\Message;

abstract class AbstractRenaissanceMessage extends Message implements RenaissanceMessageInterface
{
    protected const SENDER_NAME = 'Gabriel Attal';
    protected const SENDER_EMAIL = 'contact@parti-renaissance.fr';

    protected static function updateSenderInfo(Message $message): Message
    {
        $message->setSenderEmail(static::SENDER_EMAIL);
        $message->setSenderName(static::SENDER_NAME);

        return $message;
    }
}
