<?php

namespace AppBundle\Mailer\Message;

use AppBundle\Contact\ContactMessage;
use Ramsey\Uuid\Uuid;

final class AdherentContactMessage extends Message
{
    public static function createFromModel(ContactMessage $contactMessage): self
    {
        return new self(
            Uuid::uuid4(),
            $contactMessage->getTo()->getEmailAddress(),
            $contactMessage->getTo()->getFullName(),
            [],
            [
                'animator_firstname' => self::escape($contactMessage->getTo()->getFirstName()),
                'member_firstname' => self::escape($contactMessage->getFrom()->getFirstName()),
                'target_message' => nl2br(self::escape($contactMessage->getContent())),
            ],
            $contactMessage->getFrom()->getEmailAddress()
        );
    }
}
