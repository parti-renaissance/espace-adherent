<?php

namespace AppBundle\Mailer\Message;

use AppBundle\Contact\ContactMessage;
use AppBundle\Entity\Adherent;
use Ramsey\Uuid\Uuid;

final class AdherentContactMessage extends Message
{
    public static function create(ContactMessage $contactMessage): self
    {
        $sender = $contactMessage->getFrom();
        $recipient = $contactMessage->getTo();

        return new self(
            Uuid::uuid4(),
            $recipient->getEmailAddress(),
            $recipient->getFullName(),
            static::getTemplateVars($sender, $recipient, $contactMessage->getContent()),
            [],
            $sender->getEmailAddress()
        );
    }

    private static function getTemplateVars(Adherent $sender, Adherent $recipient, string $message): array
    {
        return [
            'recipient_first_name' => self::escape($recipient->getFirstName()),
            'sender_first_name' => self::escape($sender->getFirstName()),
            'message' => \nl2br(self::escape($message)),
        ];
    }
}
