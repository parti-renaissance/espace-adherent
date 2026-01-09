<?php

declare(strict_types=1);

namespace App\Mailer\Message\JEM;

use App\Mailer\Message\Message;
use App\Mailer\Message\Renaissance\RenaissanceMessageInterface;

abstract class AbstractJEMMessage extends Message implements RenaissanceMessageInterface
{
    protected const SENDER_NAME = 'Les Jem';

    protected static function updateSenderInfo(Message $message): Message
    {
        $message->setSenderName(static::SENDER_NAME);

        return $message;
    }
}
