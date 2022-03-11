<?php

namespace App\Mailer\Message\Assessor;

use App\Mailer\Message\Message;

abstract class AbstractAssessorMessage extends Message
{
    protected static function updateSenderInfo(Message $message): Message
    {
        $message->setSenderEmail('contact@avecvous.fr');
        $message->setSenderName('Assesseur(e) avec vous');

        return $message;
    }
}
