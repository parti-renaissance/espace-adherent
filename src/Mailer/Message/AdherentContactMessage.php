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
            self::getRecipientVars(
                $contactMessage->getFrom()->getFirstName(),
                $contactMessage->getContent()
            ),
            $contactMessage->getFrom()->getEmailAddress()
        );
    }

    private static function getRecipientVars(string $fromFirstName, string $message): array
    {
        return [
            'from_firstname' => self::escape($fromFirstName),
            'target_message' => nl2br(self::escape($message)),
        ];
    }
}
