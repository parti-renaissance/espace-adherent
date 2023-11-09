<?php

namespace App\Mailer\Message\AvecVous;

use App\Mailer\Message\Message;

abstract class AbstractAvecVousMessage extends Message
{
    protected static function updateSenderInfo(Message $message): Message
    {
        $message->setSenderEmail('ne-pas-repondre@parti-renaissance.fr');
        $message->setSenderName('Emmanuel Macron avec vous');

        return $message;
    }
}
