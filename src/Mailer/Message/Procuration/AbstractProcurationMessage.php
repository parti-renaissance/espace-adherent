<?php

declare(strict_types=1);

namespace App\Mailer\Message\Procuration;

use App\Mailer\Message\Message;
use App\ValueObject\Genders;

abstract class AbstractProcurationMessage extends Message
{
    protected const SENDER_NAME = 'Procurations • Renaissance';
    protected const SENDER_EMAIL = 'contact@parti-renaissance.fr';

    protected static function updateSenderInfo(Message $message): Message
    {
        $message->setSenderEmail(static::SENDER_EMAIL);
        $message->setSenderName(static::SENDER_NAME);

        return $message;
    }

    protected static function getCivilityName(string $gender, string $lastName): string
    {
        return \sprintf(
            '%s %s',
            Genders::FEMALE === $gender ? 'Mme' : 'M.',
            $lastName
        );
    }
}
