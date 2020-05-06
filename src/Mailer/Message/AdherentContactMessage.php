<?php

namespace App\Mailer\Message;

use App\Contact\ContactMessage;
use Ramsey\Uuid\Uuid;

final class AdherentContactMessage extends Message
{
    /**
     * @return AdherentContactMessage
     */
    public static function createFromModel(ContactMessage $contactMessage): self
    {
        return new self(
            Uuid::uuid4(),
            $contactMessage->getTo()->getEmailAddress(),
            $contactMessage->getTo()->getFullName(),
            $contactMessage->getFrom()->getFirstName().' vous a envoyé un message',
            [],
            [
                'member_firstname' => self::escape($contactMessage->getFrom()->getFirstName()),
                'target_message' => nl2br(self::escape($contactMessage->getContent())),
            ],
            $contactMessage->getFrom()->getEmailAddress()
        );
    }
}
