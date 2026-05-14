<?php

declare(strict_types=1);

namespace App\Mailer\Message\Renaissance;

use App\Contact\ContactMessage;
use Symfony\Component\Uid\Uuid;

class RenaissanceAdherentContactMessage extends AbstractRenaissanceMessage
{
    public static function createFromModel(ContactMessage $contactMessage): self
    {
        return new self(
            Uuid::v4(),
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
