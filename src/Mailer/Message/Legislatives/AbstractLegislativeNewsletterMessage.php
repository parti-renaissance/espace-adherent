<?php

namespace App\Mailer\Message\Legislatives;

use App\Mailer\Message\Message;

abstract class AbstractLegislativeNewsletterMessage extends Message
{
    protected static function updateSenderInfo(Message $message): Message
    {
        $message->setSenderEmail('ne-pas-repondre@avecvous.fr');

        return $message;
    }
}
