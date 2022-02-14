<?php

namespace App\Mailer\Message\Procuration;

use App\Mailer\Message\Message;

abstract class AbstractProcurationMessage extends Message
{
    protected static function updateSenderInfo(Message $message): Message
    {
        $message->setSenderEmail('contact@avecvous.fr');
        $message->setSenderName('avec vous');

        return $message;
    }
}
